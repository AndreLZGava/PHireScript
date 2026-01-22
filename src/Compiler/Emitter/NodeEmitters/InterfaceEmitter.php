<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\InterfaceDefinition;
use PHireScript\Helper\Debug\Debug;

class InterfaceEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof InterfaceDefinition
        && $node->type === 'interface';
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $name = $node->name;
        $extends = $node->extends ?
            ' extends ' . implode(', ', $node->extends) :
            '';
        $code = "interface {$name}{$extends} {\n";
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
