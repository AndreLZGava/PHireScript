<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\OOP;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use Exception;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceMethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Helper\Debug\Debug;

class InterfaceMethodEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof InterfaceMethodDeclarationNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {

        $modifiers = [];

        if ($node->final ?? false) {
            $modifiers[] = 'final';
        }

        if ($node->abstract ?? false) {
            $modifiers[] = 'abstract';
        }
        $modifiers = array_merge($modifiers, $node->modifiers ?? 'public');

        if ($node->static ?? false) {
            $modifiers[] = 'static';
        }

        $signature = \implode(' ', $modifiers);
        $signature .= ' function ' . $this->removeEndPunctuation($node->name);

        $params = [];
        $ctx->insideMethodSignature = true;

        foreach ($node->parameters->params ?? [] as $param) {
            $params[] = $ctx->emitter->emit($param, $ctx);
        }

        $ctx->insideMethodSignature = false;

        $signature .= '(' . \implode(', ', $params) . ')';


        $phpReturnType = $ctx->emitter->emit($node->returnType, $ctx);

        return "{$signature}{$phpReturnType};\n\n";
    }
}
