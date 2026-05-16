<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\OOP;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;

class WithNode extends ComplexObjectDefinition
{
    public function __construct(public Token $token, public array $children = [])
    {
    }
}
