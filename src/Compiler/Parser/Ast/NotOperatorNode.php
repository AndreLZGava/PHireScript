<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class NotOperatorNode extends Statement
{
    public function __construct(
        public mixed $expression
    ) {
    }
}
