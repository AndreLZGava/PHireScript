<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class NamespaceStatement extends Statement
{
    public function __construct(
        public readonly string $namespace,
        public ?string $alias = null,
    ) {
    }
}
