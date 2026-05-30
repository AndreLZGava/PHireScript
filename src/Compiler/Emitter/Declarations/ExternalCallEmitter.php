<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;

class ExternalCallEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof FunctionNode &&
            ($node->isExternalMethodCall || $node->isExternalInstantiation);
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        assert($node instanceof FunctionNode);
        if ($node->isExternalInstantiation) {
            return $this->emitInstantiation($node, $ctx);
        }
        $code = $this->emitMethodCall($node, $ctx);
        return $ctx->insideExpression ? $code : $code . ";\n";
    }

    private function emitInstantiation(FunctionNode $node, EmitContext $ctx): string
    {
        $className = $this->resolveClassName($node->variableBase, $ctx);
        $args      = $this->buildArgs($node, $ctx);
        return "new {$className}({$args})";
    }

    private function emitMethodCall(FunctionNode $node, EmitContext $ctx): string
    {
        $methodName = $node->externalMethodName;
        $args       = $this->buildArgs($node, $ctx);

        // Determine access style: class name access vs variable access
        $base = $node->variableBase;

        if ($base instanceof LiteralNode) {
            $className  = (string) $base->value;
            $descriptor = $ctx->symbolTable?->getExternal($className);

            if ($descriptor !== null) {
                $method = $descriptor->getMethod($methodName);
                if ($method !== null) {
                    if ($method->isStatic) {
                        return "{$className}::{$methodName}({$args})";
                    }
                    return "(new {$className}())->{$methodName}({$args})";
                }
            }
            // Fallback: emit as static
            return "{$className}::{$methodName}({$args})";
        }

        if ($base instanceof VariableDeclarationNode || $base instanceof VariableReferenceNode) {
            return "\${$base->name}->{$methodName}({$args})";
        }

        // Fallback
        $baseEmitted = $ctx->emitter->emit($base, $ctx);
        return "{$baseEmitted}->{$methodName}({$args})";
    }

    private function resolveClassName(mixed $base, EmitContext $ctx): string
    {
        if ($base instanceof LiteralNode) {
            return (string) $base->value;
        }
        if ($base === null) {
            return '';
        }
        return $ctx->emitter->emit($base, $ctx);
    }

    private function buildArgs(FunctionNode $node, EmitContext $ctx): string
    {
        if ($node->params === null) {
            return '';
        }
        $parts = [];
        foreach ($node->params->params as $param) {
            $parts[] = $ctx->emitter->emit($param, $ctx);
        }
        return implode(', ', $parts);
    }
}
