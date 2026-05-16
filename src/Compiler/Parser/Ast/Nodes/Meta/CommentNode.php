<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Meta;

use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class CommentNode extends Node
{
    public string $code;
}
