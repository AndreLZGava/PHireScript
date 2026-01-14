<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ArrayLiteralNode;

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
