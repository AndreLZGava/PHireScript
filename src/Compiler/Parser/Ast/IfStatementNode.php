<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class IfStatementNode extends Statement
{
    public function __construct(
        public mixed $condition,
        public mixed $statements,
        public array $elseStatements = []
    ) {
    }
}
