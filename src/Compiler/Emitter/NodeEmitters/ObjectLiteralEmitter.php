<?php

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Helper\Debug\Debug;

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

        if ($node->properties instanceof ArrayLiteralNode) {
            return '(object) ' . $ctx->emitter->emit($node->properties, $ctx);
        }

        $props = [];
        //Debug::show($node->properties);exit;
        foreach ($node->properties as $prop) {
            $props[] = $ctx->emitter->emit($prop, $ctx);
        }

        return '(object) [' . implode(', ', $props) . ']';
    }
}
