<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\AlwaysScopeNode;

class AlwaysNode extends Node
{
    public function __construct(public Token $token, public ?AlwaysScopeNode $scope = null)
    {
    }
}
