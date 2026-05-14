<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceExtendsNode;

class InterfaceNode extends ComplexObjectDefinition
{
    public array $modifiers = [];
    public ?InterfaceExtendsNode $extends = null;
    public ?InterfaceBodyNode $body = null;
    public function __construct(public Token $token)
    {
    }
}
