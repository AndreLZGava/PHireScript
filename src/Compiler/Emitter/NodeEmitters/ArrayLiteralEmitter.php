<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;

class ArrayLiteralEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ArrayLiteralNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $items = [];

        foreach ($node->elements ?? [] as $el) {
            $items[] = $ctx->emitter->emit($el, $ctx);
        }

        return '[' . implode(', ', $items) . ']';
    }
}
