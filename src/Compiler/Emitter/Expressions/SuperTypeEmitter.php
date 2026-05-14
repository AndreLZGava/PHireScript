<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\SuperTypeNode;

class SuperTypeEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof SuperTypeNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $ctx->uses->add("PHireScript\Runtime\Types\SuperTypes\\{$node->type}");
        $value = \is_null($node->value) ? $node->value : $ctx->emitter->emit($node->value, $ctx);
        return "{$node->type}::cast({$value})";
    }
}
