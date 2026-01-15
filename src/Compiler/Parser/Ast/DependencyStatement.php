<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

class DependencyStatement extends Statement
{
    public function __construct(
        public readonly string $package,
        public ?string $alias = null,
    ) {
    }
}
