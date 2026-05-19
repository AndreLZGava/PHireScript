<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Compiler\DependencyGraphBuilder\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class DependencyGraphBuilder
{
    public function __construct()
    {
    }

    /** @var array<string, Node> */
    private array $nodes = [];

    /** @var array<string, array<string>> */
    private array $edges = [];

    private array $config = [];

    public function buildGraph(array $astList, $config): void
    {
        $this->config = $config;
        foreach ($astList as $ast) {
            $this->registerNode($ast);
        }

        foreach ($astList as $ast) {
            $this->registerEdges($ast);
        }

        $this->validateGraph();
    }

    private function registerNode(Program $ast): void
    {
        foreach ($ast->statements as $stmt) {
            if ($stmt instanceof PackageNode) {
                $packageName = $stmt->completePackage;
                if (isset($this->nodes[$packageName])) {
                    throw new CompileException(
                        "Package '{$packageName}' was already defined!",
                        $stmt->token->line,
                        $stmt->token->column,
                    );
                }

                $this->nodes[$packageName] = new Node(
                    package: $packageName,
                    file: $stmt->file,
                    dependsOn: [],
                    namespace: $stmt->namespace . '\\' . $stmt->object,
                );
            }
        }
    }

    private function registerEdges(Program $ast): void
    {
        $currentPackage = null;
        $shouldHavePackage = false;
        foreach ($ast->statements as $stmt) {
            if ($stmt instanceof PackageNode) {
                $currentPackage = $stmt->completePackage;
            }
            if (
                $stmt instanceof ClassNode ||
                $stmt instanceof InterfaceNode
            ) {
                $shouldHavePackage = true;
                break;
            }
        }

        if ($shouldHavePackage && !$currentPackage) {
            throw new \RuntimeException("File does not have a package defined!");
        }

        foreach ($ast->statements as $stmt) {
            if ($stmt instanceof UseNode) {
                foreach ($stmt->packages as $dep) {
                    $depPackage = $dep->package;

                    if (!isset($this->nodes[$depPackage])) {
                        $file = $currentPackage && isset($this->nodes[$currentPackage])
                            ? $this->nodes[$currentPackage]->file
                            : 'unknown file';

                        throw new \RuntimeException(
                            "Dependency '{$depPackage}' not found!\n" .
                            "Required by package '{$currentPackage}' in file '{$file}'"
                        );
                    }

                    $this->nodes[$currentPackage]->dependsOn[] = $depPackage;

                    if (!isset($this->edges[$depPackage])) {
                        $this->edges[$depPackage] = [];
                    }
                    $this->edges[$depPackage][] = $currentPackage;
                }
            }
        }
    }


    private function validateGraph(): void
    {
        $visited = [];
        $stack   = [];

        foreach (array_keys($this->nodes) as $pkg) {
            if (isset($visited[$pkg])) {
                continue;
            }

            /** @var array<int, string> $cycle */
            $cycle = [];

            if ($this->hasCycle($pkg, $visited, $stack, $cycle)) {
                $path = implode(' → ', array_reverse($cycle));
                throw new \RuntimeException("Circular dependency detected: {$path}");
            }
        }
    }

    /**
     * @param array<string, bool> $visited
     * @param array<string, bool> $stack
     * @param array<int, string>  $cycle
     */
    private function hasCycle(
        string $node,
        array &$visited,
        array &$stack,
        array &$cycle,
    ): bool {
        $visited[$node] = true;
        $stack[$node]   = true;

        foreach ($this->nodes[$node]->dependsOn as $rawDep) {
            $dep = (string) $rawDep;

            if (!isset($this->nodes[$dep])) {
                continue;
            }

            if (!isset($visited[$dep])) {
                if ($this->hasCycle($dep, $visited, $stack, $cycle)) {
                    $cycle[] = $node;
                    return true;
                }
            } elseif (isset($stack[$dep])) {
                $cycle = [$dep, $node];
                return true;
            }
        }

        unset($stack[$node]);
        return false;
    }

    public function getCompilationOrder(): array
    {
        return $this->topologicalSort();
    }


    private function topologicalSort(): array
    {
        $inDegree = [];
        foreach ($this->nodes as $package => $node) {
            $inDegree[$package] = 0;
        }

        foreach ($this->nodes as $node) {
            foreach ($node->dependsOn as $dep) {
                $inDegree[$node->package]++;
            }
        }

        $queue = [];
        foreach ($inDegree as $pkg => $deg) {
            if ($deg === 0) {
                $queue[] = $pkg;
            }
        }

        $result = [];
        while ($queue) {
            $pkg = array_shift($queue);
            $result[] = $pkg;

            foreach ($this->edges[$pkg] ?? [] as $dependent) {
                $inDegree[$dependent]--;
                if ($inDegree[$dependent] === 0) {
                    $queue[] = $dependent;
                }
            }
        }

        if (\count($result) !== \count($this->nodes)) {
            throw new \RuntimeException("Cyclic dependency found!");
        }

        return $result;
    }

    public function getDependenciesOf($package): array
    {
        if (!isset($this->getNodes()[$package])) {
            return [];
        }
        return $this->getNodes()[$package]?->dependsOn;
    }
    // @todo at some point we'll need to provide a way to handle alias.
    public function isDependencyOf($currentPackage, $package): bool
    {
        foreach ($this->getDependenciesOf($currentPackage) as $item) {
            if (\is_string($item) && \str_ends_with($item, (string) $package)) {
                return true;
            }
        }

        return false;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    /** @return array<string, array<string>> */
    public function getEdges(): array
    {
        return $this->edges;
    }

    /**
     * Returns a package → file-path map for all registered packages.
     *
     * @return array<string, string>
     */
    public function getPackageToFileMap(): array
    {
        $map = [];

        foreach ($this->nodes as $pkg => $node) {
            $map[$pkg] = $node->file;
        }

        return $map;
    }

    /**
     * Serialize the current graph state for persistent caching.
     *
     * @return array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>}
     */
    public function exportForCache(): array
    {
        return [
            'nodes'  => $this->nodes,
            'edges'  => $this->edges,
            'config' => $this->config,
        ];
    }

    /**
     * Restore graph state from a value previously returned by exportForCache().
     *
     * @param array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>} $data
     */
    public function restoreFromCache(array $data): void
    {
        /** @var array<string, Node> $nodes */
        $nodes = $data['nodes'];
        $this->nodes  = $nodes;
        $this->edges  = $data['edges'];
        $this->config = $data['config'];
    }

    public function isReady(): bool
    {
        return $this->nodes !== [];
    }

    /**
     * Returns true when any Node in the cached graph references a file that
     * no longer exists on disk.  Used to decide whether to discard the cache.
     *
     * @param array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>} $graphData
     */
    public static function hasOrphanedNodes(array $graphData): bool
    {
        foreach ($graphData['nodes'] as $node) {
            if (!($node instanceof Node)) {
                return true;
            }

            if (!file_exists($node->file)) {
                return true;
            }
        }

        return false;
    }
}
