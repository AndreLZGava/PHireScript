<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class ExpressionStatementNode extends Statement
{
    public function __construct(
        public Expression $expression
    ) {
    }
}
