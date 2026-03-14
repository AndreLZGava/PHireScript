<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\InterfaceBodyNode;
use PHireScript\Helper\Debug\Debug;

class InterfaceBodyEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof InterfaceBodyNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = "{\n";
        foreach ($node->children as $member) {
            $code .= $ctx->emitter->emit($member, $ctx);
        }

        return $code . "}\n";
    }
}
