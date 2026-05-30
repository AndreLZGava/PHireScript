<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;

class PropertyAccessEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyAccessNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $property = $node->property;

        // Check if the object is a class name registered as external
        $className = $this->resolveClassName($node->object);
        if ($className !== null && $ctx->symbolTable?->isExternalClass($className)) {
            return $this->emitExternalClassAccess($className, $property, $ctx);
        }

        // Check if the object is a variable whose inferred type is an ExternalClassDescriptor
        if ($node->object instanceof VariableNode && $ctx->symbolTable !== null) {
            $inferredType = $ctx->symbolTable->getType($node->object->name);
            if ($inferredType instanceof ExternalClassDescriptor) {
                return $this->emitExternalInstanceAccess($node->object->name, $property, $inferredType, $ctx);
            }
        }

        $object = $ctx->emitter->emit($node->object, $ctx);
        return $object . '->' . $property;
    }

    private function resolveClassName(mixed $object): ?string
    {
        // LiteralNode or similar: the object is the class name itself (not a variable)
        if (is_object($object) && property_exists($object, 'value') && is_string($object->value)) {
            return $object->value;
        }
        if (is_string($object)) {
            return $object;
        }
        return null;
    }

    private function emitExternalClassAccess(string $className, string|object $property, EmitContext $ctx): string
    {
        $propName   = is_string($property) ? $property : $ctx->emitter->emit($property, $ctx);
        $descriptor = $ctx->symbolTable?->getExternal($className);

        if ($descriptor === null) {
            return $className . '->' . $propName;
        }

        // Priority: constant > static method > instance method
        if ($descriptor->hasConstant($propName)) {
            return $className . '::' . $propName;
        }

        $method = $descriptor->getMethod($propName);
        if ($method !== null) {
            return $method->isStatic
                ? $className . '::' . $propName
                : '(new ' . $className . '())->' . $propName;
        }

        // Unknown member — emit as static access (checker will have validated)
        return $className . '::' . $propName;
    }

    private function emitExternalInstanceAccess(
        string $varName,
        string|object $property,
        ExternalClassDescriptor $descriptor,
        EmitContext $ctx
    ): string {
        $propName = is_string($property) ? $property : $ctx->emitter->emit($property, $ctx);
        return '$' . $varName . '->' . $propName;
    }
}
