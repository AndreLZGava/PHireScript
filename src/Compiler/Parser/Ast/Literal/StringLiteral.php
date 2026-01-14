<?php

namespace PHPScript\Compiler\Parser\Ast\Literal;

class StringLiteral
{
    public function __construct(
        public string $value,
        public ?int $line = null
    ) {
    }
}
