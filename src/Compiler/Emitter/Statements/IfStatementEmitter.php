<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\IfNode;
use PHireScript\Helper\Debug\Debug;

class IfStatementEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof IfNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $condition = $ctx->emitter->emit($node->condition->children[0], $ctx);

        $body = $this->emitBody($node->statements->children, $ctx);

        $code = "if ($condition) {\n $body\n}";

        foreach ($node->elseIfClauses as $elseIfClause) {
            $elseIfCondition = $ctx->emitter->emit($elseIfClause->condition->children[0], $ctx);
            $elseIfBody = $this->emitBody($elseIfClause->statements->children, $ctx);
            $code .= " elseif ($elseIfCondition) {\n $elseIfBody\n}";
        }

        if ($node->elseStatements !== null) {
            $elseBody = $this->emitBody($node->elseStatements->children, $ctx);
            $code .= " else {\n $elseBody\n}";
        }

        return $code;
    }

    private function emitBody(array $nodes, EmitContext $ctx): string
    {
        return \implode("\n", \array_map(
            fn ($n) => $ctx->emitter->emit($n, $ctx),
            $nodes
        ));
    }
}
