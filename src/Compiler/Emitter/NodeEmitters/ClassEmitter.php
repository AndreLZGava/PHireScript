<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\Internal\ConstructorEmitter;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassNode;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\PropertyNode;
use PHireScript\Helper\Debug\Debug;

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
        $extends = $node->extends ? ' extends ' . $node->extends  : '';
        $implements = $node->implements ?
            ' implements ' . implode(', ', $node->implements) :
            '';
        $code .= "class {$node->name}{$extends}{$implements}\n";

        foreach ($node->traits as $trait) {
            $code .= '    use ' . $trait . ";\n";
        }
        // Process Body
        $code .= $ctx->emitter->emit($node->body, $ctx);
        /*
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

    private function shouldGenerateConstructor(ClassNode $class): bool
    {
        foreach ($class->body as $member) {
            if ($member instanceof PropertyNode) {
                return true;
            }
        }
        return false;
    }
}
