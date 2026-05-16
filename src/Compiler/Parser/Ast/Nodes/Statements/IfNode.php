<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\IfConditionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\IfScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\ElseScopeNode;

class IfNode extends Statement
{
    public function __construct(
        public Token $token,
        public ?IfConditionNode $condition = null,
        public ?IfScopeNode $statements = null,
        public ?ElseScopeNode $elseStatements = null
    ) {
    }
}
