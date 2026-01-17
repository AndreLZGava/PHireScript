<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;

class ParameterEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyDefinition
            && $ctx->insideMethodSignature;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $type = $ctx->types->phpType($node);
        return "{$type} \${$node->name}";
    }
}
