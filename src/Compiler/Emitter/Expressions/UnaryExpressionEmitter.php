<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\UnaryExpressionNode;

class UnaryExpressionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof UnaryExpressionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $operand = $ctx->emitter->emit($node->operand, $ctx);
        return "{$node->operator}{$operand}";
    }
}
