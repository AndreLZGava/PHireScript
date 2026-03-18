<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassNode;

class ClassEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ClassNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = $node->readOnly ? 'readonly ' : '';
        $code .= implode(' ', $node->modifiers) . ' ';
        $extends = $node->extends ? ' extends ' . $node->extends->child  : '';
        $implements = $node->implements ?
            ' implements ' . implode(', ', $node->implements->children) :
            '';
        $code .= "class {$node->name}{$extends}{$implements}\n";
        $code .= $ctx->emitter->emit($node->body, $ctx);
        /*
        @todo this will be properly emitted by its own emitters.
        // ---- properties
        foreach ($node->body as $member) {
            if ($member instanceof PropertyNode) {
                $code .= $ctx->emitter->emit($node->body, $ctx);
            }
        }

        // ---- constructor
        if ($this->shouldGenerateConstructor($node)) {
            $code .= (new ConstructorEmitter())->emit($node, $ctx);
        }

        // ---- methods
        foreach ($node->body as $member) {
            if ($member instanceof MethodDefinition) {
                $code .= $ctx->emitter->emit($member, $ctx);
            }
        }
        return $code . "}\n";
*/
        return $code;
    }
}
