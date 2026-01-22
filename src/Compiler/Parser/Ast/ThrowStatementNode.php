<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class ThrowStatementNode extends Statement
{
    public function __construct(
        public mixed $exceptionExpression
    ) {
    }
}
