<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\AssignmentNode;

class AssignmentEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof AssignmentNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $left  = $ctx->emitter->emit($node->left, $ctx);
        $right = $ctx->emitter->emit($node->right, $ctx);

        return "{$left} = {$right};";
    }
}
