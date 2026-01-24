<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class NumberNode extends Expression
{
    public function __construct(
        public float|int $value,
    ) {
    }
}
