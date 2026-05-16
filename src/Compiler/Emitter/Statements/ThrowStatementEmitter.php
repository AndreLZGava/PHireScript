<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ThrowStatementNode;

class ThrowStatementEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ThrowStatementNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $expression = $ctx->emitter->emit($node->exceptionExpression, $ctx);

        return "throw {$expression};";
    }
}
