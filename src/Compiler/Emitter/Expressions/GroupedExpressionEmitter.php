<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\GroupedExpressionNode;

class GroupedExpressionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof GroupedExpressionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $inner = $ctx->emitter->emit($node->inner, $ctx);
        return "({$inner})";
    }
}
