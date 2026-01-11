<?php

namespace PHPScript\Compiler;

use Exception;
use PHPScript\SymbolTable;
use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Helper\Debug\Debug;

class Checker
{
    private $table;
    public function check(Program $ast, SymbolTable $table)
    {
        $this->table = $table;
        foreach ($ast->statements as $node) {
            if ($node instanceof ClassDefinition) {
                $this->checkClassBody($node, $table);
            }
        }
    }

    private function checkClassBody($classNode)
    {
        foreach ($classNode->body as $member) {
            if ($member instanceof PropertyDefinition) {
                if ($member->defaultValue !== null) {
                    // $this->ensureTypeCompatibility($member, $member->defaultValue);
                }
            }

            if ($member instanceof MethodDefinition) {
                $this->validateMethodReturn($member);
            }
        }
    }

    private function validateMethodReturn(MethodDefinition $method)
    {
        $this->ensureReturnsForMethods($method);

        foreach ($method->bodyCode as $node) {
            if ($node instanceof \PHPScript\Compiler\Parser\Ast\ReturnNode) {
                $this->checkTypeCompatibility($method->returnType, $node->expression, $method->name);
            }
        }
    }

    private function checkTypeCompatibility($declaredType, $expressionNode, $methodName)
    {
        if ($expressionNode instanceof \PHPScript\Compiler\Parser\Ast\ArrayLiteralNode) {
            if ($declaredType === 'Array') {
                return true;
            }

            if (str_starts_with($declaredType, '[') && str_ends_with($declaredType, ']')) {
                $innerTypes = trim($declaredType, '[]');
                $allowedTypes = explode('|', $innerTypes);

                foreach ($expressionNode->elements as $index => $element) {
                    $elementType = $this->getNodeType($element);

                    if (!in_array($elementType, $allowedTypes)) {
                        throw new \Exception(
                            "Semantic Error in method '{$methodName}': " .
                                "Element at index {$index} is of type '{$elementType}', " .
                                "but the return array only accepts [" . implode('|', $allowedTypes) . "]."
                        );
                    }
                }
                return true;
            }
        }

        return true;
    }

    private function ensureReturnsForMethods(MethodDefinition $prop)
    {
        $returnMethod = explode('|', $prop->returnType);
        if (
            $prop->mustBeBool && count($returnMethod) > 1 ||
            $prop->mustBeBool && current($returnMethod) !== 'Bool'
        ) {
            throw new Exception('Method ' . $prop->name .
                '? must return exclusively "Bool". Passed "' .
                $prop->returnType . '"!');
        }

        if (
            $prop->mustBeVoid && count($returnMethod) > 1 ||
            $prop->mustBeVoid && current($returnMethod) !== 'Void'
        ) {
            throw new Exception('Method ' . $prop->name .
                '! must return exclusively "Void". Passed "' .
                $prop->returnType . '"!');
        }
    }


    private function ensureTypeCompatibility(PropertyDefinition $prop, $valueNode)
    {
        $isValid = false;

        foreach ($prop->resolvedTypeInfo as $typeInfo) {
            if ($this->isCompatible($typeInfo, $valueNode)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            throw new \Exception("Semantic Error: Property '{$prop->name}' does not support the assigned type.");
        }
    }

    private function isCompatible(array $typeInfo, $valueNode): bool
    {
        switch ($typeInfo['category']) {
            case 'primitive':
                return $this->checkPrimitive($typeInfo['native'], $valueNode);
            case 'supertype':
                return $this->checkPrimitive('string', $valueNode);
            case 'metatype':
                return true;
            case 'custom':
                return true;
            default:
                return false;
        }
    }

    private function checkPrimitive(string $nativeType, $valueNode): bool
    {
        $nodeType = $this->getNodeType($valueNode);

        if ($nodeType === 'unknown') {
            return true;
        }

        return match ($nativeType) {
            'string' => $nodeType === 'String',
            'int'    => $nodeType === 'Int',
            'float'  => $nodeType === 'Float',
            'bool'   => $nodeType === 'Bool',
            'array'  => $nodeType === 'Array',
            'object' => $nodeType === 'Object' || $nodeType === 'Custom',
            default  => false
        };
    }


    private function getNodeType($node): string
    {

        if ($node instanceof \PHPScript\Compiler\Parser\Ast\LiteralNode) {
            return $node->rawType;
        }

        if ($node instanceof \PHPScript\Compiler\Parser\Ast\ArrayLiteralNode) {
            return 'Array';
        }

        if ($node instanceof \PHPScript\Compiler\Parser\Ast\VariableDeclarationNode) {
            return $this->table->getType($node->name, $node->line);
        }

        return 'unknown';
    }
}
