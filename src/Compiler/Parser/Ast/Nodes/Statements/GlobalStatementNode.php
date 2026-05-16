<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class GlobalStatementNode extends Node
{
    public string $code;
}
