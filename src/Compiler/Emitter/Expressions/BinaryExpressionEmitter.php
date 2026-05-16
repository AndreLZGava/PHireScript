<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BinaryExpressionNode;
use PHireScript\Helper\Debug\Debug;

class BinaryExpressionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof BinaryExpressionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $left = $ctx->emitter->emit($node->left, $ctx);
        $right = $ctx->emitter->emit($node->right, $ctx);

        return "{$left} {$node->operator} {$right}";
    }
}
