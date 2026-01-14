<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

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
