<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Helper\Debug\Debug;

class VariableDeclarationEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof VariableDeclarationNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $name = '$' . $node->name;
        $value = $ctx->emitter->emit($node->value, $ctx);
        return "{$name} = {$value};\n";
    }
}
