<?php

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;

class ObjectLiteralEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ObjectLiteralNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        if (empty($node->properties)) {
            return '(object) []';
        }

        $props = [];
        foreach ($node->properties as $prop) {
            $props[] = $ctx->emitter->emit($prop, $ctx);
        }

        return '(object) [' . implode(', ', $props) . ']';
    }
}
