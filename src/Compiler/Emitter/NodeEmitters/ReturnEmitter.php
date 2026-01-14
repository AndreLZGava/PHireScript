<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ReturnNode;

class ReturnEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ReturnNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $expr = $node->expression
        ? $ctx->emitter->emit($node->expression, $ctx)
        : '';

        if (
            $ctx->dev &&
            is_string($ctx->currentMethodReturnType) &&
            str_starts_with($ctx->currentMethodReturnType, '[')
        ) {
            $inner = trim($ctx->currentMethodReturnType, '[]');
            $types = "['" . implode("','", explode('|', $inner)) . "']";

            $ctx->uses->add('PHPScript\\Runtime\\Types\\TypeGuard');

            return "return TypeGuard::validateArray($expr, $types);";
        }

        return "return $expr;";
    }
}
