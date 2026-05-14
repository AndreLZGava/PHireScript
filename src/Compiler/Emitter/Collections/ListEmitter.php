<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Collections;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Collections\ListNode;

class ListEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ListNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return '[]';
    }
}
