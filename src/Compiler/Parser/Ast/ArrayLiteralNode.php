<?php

namespace PHPScript\Compiler\Parser\Ast;

class ArrayLiteralNode extends Expression
{
    public function __construct(
        public array $elements
    ) {
    }
}
