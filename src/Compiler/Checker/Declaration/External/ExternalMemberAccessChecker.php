<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration\External;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\Exceptions\CompileException;

#[CompilerPass(order: 6)]
class ExternalMemberAccessChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof PropertyAccessNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof PropertyAccessNode);

        $table    = $checker->table;
        $property = is_string($node->property) ? $node->property : null;

        if ($property === null) {
            return;
        }

        // Case 1: access on a class name registered as external
        $className = $this->resolveClassName($node->object);
        if ($className !== null && $table->isExternalClass($className)) {
            $descriptor = $table->getExternal($className);
            if ($descriptor !== null) {
                $this->validateMember($descriptor, $property, $className, $node);
            }
            return;
        }

        // Case 2: access on a variable whose inferred type is an ExternalClassDescriptor
        if ($node->object instanceof VariableNode) {
            $inferredType = $table->getType($node->object->name);

            if ($inferredType instanceof ExternalClassDescriptor) {
                $this->validateMember($inferredType, $property, $inferredType->alias, $node);
                return;
            }

            if ($inferredType === 'MIXED_EXTERNAL') {
                Messenger::warning(
                    "Return type of method on '{$node->object->name}' is mixed or undeclared"
                    . " — subsequent calls will not be validated."
                );
                return;
            }

            if (is_array($inferredType)) {
                // Union type — validate against all types, warn if partial
                /** @var string[] $unionTypes */
                $unionTypes = array_filter($inferredType, 'is_string');
                $this->validateUnionTypeMember($unionTypes, $property, $node, $table);
            }
        }
    }

    private function resolveClassName(mixed $object): ?string
    {
        if (is_object($object) && property_exists($object, 'value') && is_string($object->value)) {
            return $object->value;
        }
        if (is_string($object)) {
            return $object;
        }
        return null;
    }

    private function validateMember(
        ExternalClassDescriptor $descriptor,
        string $property,
        string $contextName,
        PropertyAccessNode $node
    ): void {
        $hasMethod   = $descriptor->hasMethod($property);
        $hasConstant = $descriptor->hasConstant($property);

        if (!$hasMethod && !$hasConstant) {
            throw new CompileException(
                "Method or constant '{$property}' does not exist in external class '{$contextName}'.",
                $node->token->line,
                $node->token->column,
            );
        }
    }

    /** @param string[] $types */
    private function validateUnionTypeMember(
        array $types,
        string $property,
        PropertyAccessNode $node,
        \PHireScript\SymbolTable $table
    ): void {
        $foundIn = [];
        foreach ($types as $typeName) {
            $descriptor = $table->getExternal($typeName);
            if ($descriptor !== null && ($descriptor->hasMethod($property) || $descriptor->hasConstant($property))) {
                $foundIn[] = $typeName;
            }
        }

        if (empty($foundIn)) {
            return; // Not an external type — skip
        }

        if (count($foundIn) < count(array_filter($types, fn ($t) => $table->getExternal($t) !== null))) {
            Messenger::warning(
                "Access to '{$property}' may be invalid at runtime: it exists only in " .
                implode('|', $foundIn) . ' of the union type ' . implode('|', $types) . '.'
            );
        }
    }
}
