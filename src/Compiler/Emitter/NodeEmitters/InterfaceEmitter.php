<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ClassDefinition;

class InterfaceEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ClassDefinition
        && $node->type === 'interface';
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $name = $node->name;
        $code = "interface {$name} {\n";
        $prev = $ctx->insideInterface;
        $ctx->insideInterface = true;

        foreach ($node->body as $member) {
            $code .= $ctx->emitter->emit($member, $ctx);
        }

        $code .= "}\n\n";
        $ctx->insideInterface = $prev;

        return $code;
    }
}
