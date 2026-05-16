<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Scopes;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class IfConditionNode extends Node
{
    public function __construct(public Token $token, public array $children = [])
    {
    }
}
