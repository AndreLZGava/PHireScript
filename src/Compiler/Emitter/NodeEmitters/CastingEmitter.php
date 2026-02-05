<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Helper\Debug\Debug;

class CastingEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof CastingNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return  '(' . $node->to . ')' . $ctx->emitter->emit($node->value, $ctx);
    }
}
