<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;

class TraitNode extends ComplexObjectDefinition
{
    public ?ClassBodyNode $body = null;

    public function __construct(public Token $token)
    {
        $this->type = $token->value;
    }
}
