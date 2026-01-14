<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

class PropertyDeclarationEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyDefinition
        && $ctx->insideClass
        && !$ctx->insideMethodSignature;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $visibility = $node->modifiers[0] ?? 'public';
        $type = $ctx->types->phpType($node, $ctx);
        return "    {$visibility} {$type} \${$node->name}; \n";
    }
}
