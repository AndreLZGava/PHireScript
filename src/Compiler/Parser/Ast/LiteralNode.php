<?php

namespace PHPScript\Compiler\Parser\Ast;

class LiteralNode extends Expression
{
    public function __construct(
        public mixed $value,
        public string $rawType
    ) {
    }
}
