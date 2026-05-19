<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration\Class;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ReturnTypeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Runtime\Exceptions\CheckerException;

#[CompilerPass(order: 7)]
class MethodReturnChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof MethodDeclarationNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof MethodDeclarationNode);
        $this->ensureReturnsForMethods($node);
        $this->validateReturnTypeWithBody($node, $checker);
    }

    private function ensureReturnsForMethods(MethodDeclarationNode $method): void
    {
        if ($method->returnType === null) {
            return;
        }

        $returnTypes = $method->returnType->types;

        if (
            $method->mustBeBool && (\count($returnTypes) > 1 || (\current($returnTypes) ?: '') !== 'Bool')
        ) {
            throw new CheckerException(
                'Method ' . $method->name . '? must return exclusively "Bool". Passed "' .
                    \implode('|', $returnTypes) . '"!',
                $method->line,
                $method->column
            );
        }

        if (
            $method->mustBeVoid && (\count($returnTypes) > 1 || (\current($returnTypes) ?: '') !== 'Void')
        ) {
            throw new CheckerException(
                'Method ' . $method->name . '! must return exclusively "Void". Passed "' .
                    \implode('|', $returnTypes) . '"!',
                $method->line,
                $method->column
            );
        }
    }

    private function validateReturnTypeWithBody(MethodDeclarationNode $method, CompilerChecker $checker): void
    {
        $children = $method->bodyCode !== null ? $method->bodyCode->children : [];

        foreach ($children as $node) {
            if ($node instanceof ReturnNode) {
                $this->checkTypeCompatibility($method->returnType, $node->expression, $method->name, $checker);
            }
        }
    }

    private function checkTypeCompatibility(
        ?ReturnTypeNode $declaredType,
        mixed $expressionNode,
        string $methodName,
        CompilerChecker $checker,
    ): bool {
        if ($expressionNode instanceof ArrayLiteralNode) {
            if ($declaredType === null) {
                return true;
            }

            $typeString = \implode('|', $declaredType->types);

            if ($typeString === 'Array') {
                return true;
            }

            if (\str_starts_with($typeString, '[') && \str_ends_with($typeString, ']')) {
                $innerTypes   = \trim($typeString, '[]');
                $allowedTypes = \explode('|', $innerTypes);

                foreach ($expressionNode->elements as $index => $element) {
                    $elementType = $this->getNodeType($element, $checker);

                    if (!\in_array($elementType, $allowedTypes, true)) {
                        throw new \Exception(
                            "Semantic Error in method '{$methodName}': " .
                                "Element at index {$index} is of type '{$elementType}', " .
                                "but the return array only accepts [" . \implode('|', $allowedTypes) . "]."
                        );
                    }
                }

                return true;
            }
        }

        return true;
    }

    private function getNodeType(mixed $node, CompilerChecker $checker): string
    {
        if ($node instanceof LiteralNode) {
            return $node->rawType;
        }

        if ($node instanceof ArrayLiteralNode) {
            return 'Array';
        }

        if ($node instanceof VariableDeclarationNode) {
            $resolved = $checker->table->getType($node->name);
            return is_string($resolved) ? $resolved : 'unknown';
        }

        return 'unknown';
    }
}
