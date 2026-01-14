<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

class ArrayLiteralNode extends Expression
{
    public function __construct(
        public array $elements
    ) {
    }
}
