<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassExtendsNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ValidateBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\WithNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ImplementsNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ConstructorDefinitionNode;

class ValidateNode extends ComplexObjectDefinition
{
    public string $type;
    public bool $readOnly = false;
    public array $modifiers = [];
    public ?string $docBlock = null;
    public ?ClassExtendsNode $extends = null;
    public ?WithNode $with = null;
    public ?ImplementsNode $implements = null;
    public ?ConstructorDefinitionNode $construct = null;
    public ?ValidateBodyNode $body = null;

    public function __construct(public Token $token)
    {
        $this->type = $token->value;
    }
}
