<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Literal;

class BoolLiteral
{
    public function __construct(
        public int $value,
        public ?int $line = null
    ) {
    }
}
