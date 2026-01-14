<?php

namespace PHPScript\Compiler\Parser\Ast\Literal;

class IntLiteral
{
    public function __construct(
        public int $value,
        public ?int $line = null
    ) {
    }
}
