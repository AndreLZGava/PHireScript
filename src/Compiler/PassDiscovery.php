<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\FileManager\ClassScanner;
use ReflectionClass;

class PassDiscovery
{
    private readonly ClassScanner $scanner;

    public function __construct()
    {
        $this->scanner = new ClassScanner();
    }

    /**
     * Discovers all classes in $directory that extend/implement $baseClass and carry
     * #[CompilerPass(order: N)], then returns instances sorted by order ascending.
     *
     * @param class-string $baseClass
     * @return list<object>
     */
    public function discover(string $directory, string $baseClass): array
    {
        $fqcns = $this->scanner->listClassesExtending($directory, $baseClass);

        /** @var list<array{order: int, instance: object}> $passes */
        $passes = [];

        foreach ($fqcns as $fqcn) {
            /** @var class-string $fqcn */
            $ref   = new ReflectionClass($fqcn);
            $attrs = $ref->getAttributes(CompilerPass::class);

            if (empty($attrs)) {
                continue;
            }

            /** @var CompilerPass $pass */
            $pass = $attrs[0]->newInstance();

            /** @var object $instance */
            $instance = $ref->newInstance();

            $passes[] = ['order' => $pass->order, 'instance' => $instance];
        }

        usort($passes, fn (array $a, array $b): int => $a['order'] <=> $b['order']);

        return array_column($passes, 'instance');
    }
}
