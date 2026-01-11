<?php

namespace PHPScript\Compiler\Parser\Ast;

class ExpressionStatementNode extends Statement
{
    public function __construct(
        public Expression $expression
    ) {
    }
}
