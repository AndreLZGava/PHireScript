<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\VoidExpressionNode;

class VoidExpressionEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof VoidExpressionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return '';
    }
}
