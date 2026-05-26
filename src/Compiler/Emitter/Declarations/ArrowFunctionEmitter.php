<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ArrowFunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\MethodScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;

class ArrowFunctionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ArrowFunctionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $signature = ' function';

        $paramNames = [];
        if ($node->parameters !== null) {
            $signature .= $ctx->emitter->emit($node->parameters, $ctx);
            foreach ($node->parameters->params as $param) {
                if (\is_string($param->name)) {
                    $paramNames[] = $param->name;
                }
            }
        } else {
            $signature .= '()';
        }

        $captured = $this->collectExternalRefs($node->bodyCode, $paramNames);
        if ($captured !== []) {
            $useList = \implode(', ', \array_map(fn ($v) => '$' . $v, $captured));
            $signature .= ' use (' . $useList . ')';
        }

        if ($node->returnType !== null) {
            $signature .= $ctx->emitter->emit($node->returnType, $ctx);
        }

        return $signature . $ctx->emitter->emit($node->bodyCode, $ctx);
    }

    /**
     * @param string[] $paramNames
     * @return string[]
     */
    private function collectExternalRefs(?MethodScopeNode $body, array $paramNames): array
    {
        if ($body === null) {
            return [];
        }
        $refs = [];
        $this->collectRefs($body->children, $refs);
        return \array_values(\array_diff(\array_unique($refs), $paramNames));
    }

    /** @param object[] $nodes @param string[] $refs */
    private function collectRefs(array $nodes, array &$refs): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof VariableReferenceNode) {
                $refs[] = $node->name;
            } elseif ($node instanceof ReturnNode && $node->expression !== null) {
                $this->collectRefs([$node->expression], $refs);
            } elseif ($node instanceof MethodScopeNode) {
                $this->collectRefs($node->children, $refs);
            }
        }
    }
}
