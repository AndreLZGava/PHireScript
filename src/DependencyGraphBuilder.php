<?php

namespace PHireScript;

use PHireScript\Compiler\DependencyGraphBuilder\Node;
use PHireScript\Compiler\Parser\Ast\PackageStatement;
use PHireScript\Compiler\Parser\Ast\DependenciesStatement;
use PHireScript\Compiler\Program;

class DependencyGraphBuilder
{
    public function __construct()
    {
    }

  /** @var array<string, Node> */
    private array $nodes = [];

  /** @var array<string, array<string>> */
    private array $edges = [];

    public function buildGraph(array $astList): void
    {
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
            if ($stmt instanceof PackageStatement) {
                $packageName = $stmt->completePackage;
                if (isset($this->nodes[$packageName])) {
                    throw new \Exception("Package '{$packageName}' was already defined!");
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

        foreach ($ast->statements as $stmt) {
            if ($stmt instanceof PackageStatement) {
                $currentPackage = $stmt->completePackage;
                break;
            }
        }

        if (!$currentPackage) {
            throw new \Exception("File does not have a package defined!");
        }

        foreach ($ast->statements as $stmt) {
            if ($stmt instanceof DependenciesStatement) {
                foreach ($stmt->packages as $dep) {
                    $depPackage = $dep->package;

                    if (!isset($this->nodes[$depPackage])) {
                        throw new \Exception("Dependency '{$depPackage}' not found!");
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
      // Here i'll implement cycles check using DFS
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

        if (count($result) !== count($this->nodes)) {
            throw new \Exception("Cyclic dependency found!");
        }

        return $result;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }
}
