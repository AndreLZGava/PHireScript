<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PrimitiveCastingNode;
use PHireScript\Helper\TypeResolver;

class CastingEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PrimitiveCastingNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $cast = TypeResolver::isPrimitive($node->to) ? TypeResolver::nativeType($node->to) : $node->to;
        return '(' . $cast . ')' . $ctx->emitter->emit($node->value, $ctx);
    }
}
