<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\OOP;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Helper\Debug\Debug;

class PropertyDeclarationEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PropertyNode;
        // && $ctx->insideClass
        // && !$ctx->insideMethodSignature;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $visibility = isset($node->modifiers[0]) &&
            $node->modifiers[0] === 'abstract' ?
            'public ' :
            (empty($node->modifiers) ? 'public ' : $node->modifiers[0] . ' ');
        $type = $ctx->types->phpType($node);
        $name = '$' . $node->name;
        $defaultValue = $node->value ? ' = ' . $ctx->emitter->emit($node->value, $ctx) : '';

        return "    {$visibility}{$type} {$name}{$defaultValue};\n";
    }
}
