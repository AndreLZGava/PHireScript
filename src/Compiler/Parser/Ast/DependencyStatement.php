<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class DependencyStatement extends Statement
{
    public function __construct(
        public Token $token,
        public readonly string $package,
        public ?string $alias = null,
    ) {
    }
}
