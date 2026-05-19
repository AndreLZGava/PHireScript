<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;
use PHireScript\Helper\Debug\Debug;

class VariableManager
{
    /** @var array<int, array<string, VariableDeclarationNode|VariableReferenceNode>> */
    private array $scopes = [[]];

    private mixed $variableOnFocus = null;

    public function __construct(private array $properties = [])
    {
    }

    public function enterScope(): void
    {
        $this->scopes[] = [];
    }

    public function exitScope(): void
    {
        if (count($this->scopes) > 1) {
            array_pop($this->scopes);
        }
    }

    public function addProperty(PropertyNode $property): void
    {
        $this->properties[$property->name] = $property;
    }

    public function addVariable(VariableDeclarationNode|VariableReferenceNode $variable): void
    {
        $depth = count($this->scopes) - 1;
        $this->scopes[$depth][$variable->name] = $variable;
        $this->variableOnFocus = $variable;
    }

    /** @return array<string, VariableDeclarationNode|VariableReferenceNode> */
    public function getVariables(): array
    {
        $all = [];
        foreach ($this->scopes as $scope) {
            $all = array_merge($all, $scope);
        }
        return $all;
    }

    public function getVariable(string $variableName): null|VariableDeclarationNode|VariableReferenceNode
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$i][$variableName])) {
                $this->variableOnFocus = $this->scopes[$i][$variableName];
                return $this->scopes[$i][$variableName];
            }
        }
        return null;
    }

    /** @return array<string, PropertyNode> */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $propertyName): ?PropertyNode
    {
        return $this->properties[$propertyName] ?? null;
    }

    public function getVariableOnFocus(): mixed
    {
        return $this->variableOnFocus;
    }

    public function setVirtualVariable(mixed $variable): void
    {
        $this->variableOnFocus = $variable;
    }
}
