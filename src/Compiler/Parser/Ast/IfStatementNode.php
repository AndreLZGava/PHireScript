<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class IfStatementNode extends Statement
{
    public function __construct(
        Token $token,
        public mixed $condition,
        public mixed $statements,
        public array $elseStatements = []
    ) {
    }
}
