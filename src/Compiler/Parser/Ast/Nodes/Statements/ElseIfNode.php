<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\ElseIfScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\IfConditionNode;

class ElseIfNode extends Statement
{
    public function __construct(
        public Token $token,
        public ?IfConditionNode $condition = null,
        public ?ElseIfScopeNode $statements = null
    ) {
    }
}
