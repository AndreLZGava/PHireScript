<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\HandleNode;

class HandleEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof HandleNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = "catch";
        if ($node->param) {
            $code .= $ctx->emitter->emit($node->param, $ctx);
        }
        $code .= "{\n";
        foreach ($node->children[0]->children ?? [] as $child) {
            $code .= $ctx->emitter->emit($child, $ctx);
            $code .= "\n";
        }
        $code .= "}\n";
        return $code;
    }
}
