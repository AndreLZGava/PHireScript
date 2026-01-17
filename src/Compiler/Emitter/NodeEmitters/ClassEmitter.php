<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\Internal\ConstructorEmitter;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Helper\Debug\Debug;

class ClassEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ClassDefinition;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = $node->readOnly ? 'readonly ' : '';
        $code .= "class {$node->name} {\n";
        // ---- properties
        foreach ($node->body as $member) {
            if ($member instanceof PropertyDefinition) {
                $code .= $ctx->emitter->emit($member, $ctx) ;
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
    }

    private function shouldGenerateConstructor(ClassDefinition $class): bool
    {
        foreach ($class->body as $member) {
            if ($member instanceof PropertyDefinition) {
                return true;
            }
        }
        return false;
    }
}
