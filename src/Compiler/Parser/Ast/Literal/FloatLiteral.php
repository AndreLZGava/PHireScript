<?php

namespace PHPScript\Compiler\Parser\Ast\Literal;

class FloatLiteral
{
    public function __construct(
        public float $value,
        public ?int $line = null
    ) {
    }
}
