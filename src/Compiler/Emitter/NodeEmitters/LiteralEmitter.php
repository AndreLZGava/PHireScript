<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\LiteralNode;

class LiteralEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof LiteralNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return match ($node->rawType) {
            'String' => $node->value,
            default  => (string) $node->value
        };
    }
}
