<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassExtendsNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\WithNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ImplementsNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\DependencyInjectionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ConstructorDefinitionNode;

class ClassNode extends ComplexObjectDefinition
{
    public string $type;
    public bool $readOnly = false;
    public array $modifiers = [];
    public ?string $docBlock = null;
    public ?ClassExtendsNode $extends = null;
    public ?WithNode $with = null;
    public ?ImplementsNode $implements = null;
    public ?ConstructorDefinitionNode $construct = null;
    public array $inject = [];
    public array $cache = [];
    public array $schedule = [];
    public ?ClassBodyNode $body = null;
    public ?DependencyInjectionNode $typeDependencyInjection = null;

    public function __construct(public Token $token)
    {
        $this->type = $token->value;
    }
}
