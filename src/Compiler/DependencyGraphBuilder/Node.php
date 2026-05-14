<?php

declare(strict_types=1);

namespace PHireScript\Compiler\DependencyGraphBuilder;

class Node
{
    public bool $dirty = false;

    public function __construct(public string $package, public string $file, public $namespace, public array $dependsOn = [])
    {
    }
}
