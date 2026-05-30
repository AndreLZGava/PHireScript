<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;

class AssignmentEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof AssignmentNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $left               = $ctx->emitter->emit($node->left, $ctx);
        $ctx->insideExpression = true;
        $right              = $ctx->emitter->emit($node->right, $ctx);
        $ctx->insideExpression = false;
        return "{$left} = {$right};";
    }
}
