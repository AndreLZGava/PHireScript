<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\BoolNode;

/**
 * Apparently not used.
 */
class BoolEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof BoolNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return $node->value ? 'true' : 'false';
    }
}
