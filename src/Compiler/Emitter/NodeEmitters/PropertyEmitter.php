<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;

class PropertyEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyDefinition;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $visibility = $node->modifiers[0] ?? 'public';
        $type = $ctx->types->phpType($node);
        $name = '$' . $node->name;

        return "    {$visibility} {$type} {$name};\n";
    }
}
