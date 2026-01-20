<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\Internal\ConstructorEmitter;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\TraitDefinition;
use PHireScript\Helper\Debug\Debug;

class TraitEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof TraitDefinition;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = "trait {$node->name} {\n";
        // ---- properties
        foreach ($node->body as $member) {
            if ($member instanceof PropertyDefinition) {
                $code .= $ctx->emitter->emit($member, $ctx) ;
            }
        }

        // ---- methods
        foreach ($node->body as $member) {
            if ($member instanceof MethodDefinition) {
                $code .= $ctx->emitter->emit($member, $ctx);
            }
        }

        return $code . "}\n";
    }
}
