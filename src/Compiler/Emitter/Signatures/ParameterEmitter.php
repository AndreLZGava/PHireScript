<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Signatures;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;

class ParameterEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyNode
            && $ctx->insideMethodSignature;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $type = $ctx->types->phpType($node);
        return "{$type} \${$node->name}";
    }
}
