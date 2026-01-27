<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;

class VariableManager
{
    public function __construct(
        private array $variables = [],
        private array $properties = [],
    ) {
    }

    public function addProperty(PropertyDefinition $property)
    {
        $this->properties[$property->name] = $property;
    }

    public function addVariable(VariableDeclarationNode|VariableReferenceNode $variable)
    {
        $this->variables[$variable->name] = $variable;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function getVariable(string $variableName): null|VariableDeclarationNode|VariableReferenceNode
    {
        return $this->variables[$variableName]  ?? null;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getProperty(string $propertyName): ?PropertyDefinition
    {
        return $this->properties[$propertyName]  ?? null;
    }
}
