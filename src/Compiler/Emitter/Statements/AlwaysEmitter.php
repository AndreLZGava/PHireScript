<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AlwaysNode;

class AlwaysEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof AlwaysNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = "finally\n";
        $code .= "{\n";
        foreach ($node->scope->children as $child) {
            $code .= $ctx->emitter->emit($child, $ctx);
            $code .= "\n";
        }
        $code .= "}\n";
        return $code;
    }
}
