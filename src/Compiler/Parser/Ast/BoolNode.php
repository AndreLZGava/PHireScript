<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class BoolNode extends Expression
{
    public function __construct(
        public bool $value,
    ) {
    }
}
