<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\PropertyAccessNode;

class PropertyAccessEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyAccessNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $object = $ctx->emitter->emit($node->object, $ctx);
        return $object . '->' . $node->property;
    }
}
