<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ThisExpressionNode;

class ThisExpressionEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ThisExpressionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return '$this';
    }
}
