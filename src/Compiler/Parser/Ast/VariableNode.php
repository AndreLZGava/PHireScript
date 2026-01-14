<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

class VariableNode extends Statement
{
    public function __construct(
        public string $name,
    ) {
    }
}
