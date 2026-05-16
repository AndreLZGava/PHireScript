<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Helper\Debug\Debug;

class InterfaceEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof InterfaceNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $name = $node->name;
        $extends = $node->extends ?
        ' extends ' . \implode(', ', $node?->extends?->children ?? []) :
        '';
        $code = "interface {$name}{$extends}\n";
        $prev = $ctx->insideInterface;
        $ctx->insideInterface = true;
        $code .= $ctx->emitter->emit($node->body, $ctx);
        $ctx->insideInterface = $prev;

        return $code;
    }
}
