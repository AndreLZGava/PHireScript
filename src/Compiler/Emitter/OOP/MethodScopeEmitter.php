<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\OOP;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use Exception;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\MethodScopeNode;
use PHireScript\Helper\Debug\Debug;

class MethodScopeEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof MethodScopeNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {

        $code = "{\n";

        foreach ($node->children ?? [] as $stmt) {
            $code .= $ctx->emitter->emit($stmt, $ctx) . "\n";
        }
        $code .= "}\n\n";

        return $code;
    }
}
