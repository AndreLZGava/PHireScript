<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\OOP;

use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class ConstructorDefinitionNode extends Node
{
    public function __construct(
        public array $modifiers = [],
        public array $params = [],
        public array $body = [],
    ) {
    }
}
