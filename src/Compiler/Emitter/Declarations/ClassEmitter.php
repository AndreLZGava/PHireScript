<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\OOP\ConstructorEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeUsageNode;
use PHireScript\Helper\Debug\Debug;

class ClassEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ClassNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = '';
        foreach ($node->attributes as $attr) {
            if ($attr instanceof AttributeUsageNode) {
                $code .= $ctx->emitter->emit($attr, $ctx);
            }
        }

        $code .= $node->readOnly ? 'readonly ' : '';
        $code .= \implode(' ', $node->modifiers) . ' ';
        $extends = $node->extends ? ' extends ' . $node->extends->child : '';
        $implements = $node->implements ?
            ' implements ' . \implode(', ', $node->implements->children) :
            '';
        $code .= "class {$node->name}{$extends}{$implements}\n";
        $code .= $ctx->emitter->emit($node->body, $ctx);
        return $code;
    }
}
