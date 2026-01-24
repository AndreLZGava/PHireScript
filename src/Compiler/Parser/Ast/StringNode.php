<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class StringNode extends Expression
{
    public function __construct(
        public string $value,
    ) {
    }
}
