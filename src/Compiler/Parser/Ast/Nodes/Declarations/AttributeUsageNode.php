<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;

class AttributeUsageNode extends Node
{
    public function __construct(
        public Token $token,
        public string $name,
        public ?ParamsNode $params = null,
    ) {
    }
}
