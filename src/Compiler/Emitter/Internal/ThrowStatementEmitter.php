<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Internal;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ThrowStatementNode;

class ThrowStatementEmitter implements NodeEmitter
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
