<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;

class AssignmentNode extends Statement
{
    public function __construct(
        Token $token,
        public Node $left,
        public ?Node $right = null
    ) {
    }

    public function addChild(Node $node): void
    {
        $this->right = $node;
    }
}
