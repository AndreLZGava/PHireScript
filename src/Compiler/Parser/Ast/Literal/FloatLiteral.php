<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Literal;

class FloatLiteral
{
    public function __construct(
        public float $value,
        public ?int $line = null
    ) {
    }
}
