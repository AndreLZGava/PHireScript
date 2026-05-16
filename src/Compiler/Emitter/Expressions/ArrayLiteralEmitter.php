<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Meta\CommentNode;
use PHireScript\Helper\Debug\Debug;

class ArrayLiteralEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ArrayLiteralNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $items = [];
        foreach ($node->elements ?? [] as $n => $el) {
            if (!$el instanceof CommentNode) {
                $items[] = $ctx->emitter->emit($el, $ctx);
            }
        }

        return "[\n" . \implode(", \n", $items) . "\n]";
    }
}
