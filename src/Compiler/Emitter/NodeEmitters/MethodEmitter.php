<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use Exception;
use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\MethodDeclarationNode;
use PHireScript\Helper\Debug\Debug;

class MethodEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof MethodDeclarationNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $indent = '    ';

        // --------------------
        // modifiers
        // --------------------
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

        $signature = implode(' ', $modifiers);
        $signature .= ' function ' . $node->name;

        // --------------------
        // params
        // --------------------
        $params = [];
        $ctx->insideMethodSignature = true;

        foreach ($node->args ?? [] as $param) {
            $params[] = $ctx->emitter->emit($param, $ctx);
        }
        $ctx->insideMethodSignature = false;

        $signature .= '(' . implode(', ', $params) . ')';

        // --------------------
        // return type (PHP)
        // --------------------
        $phpReturnType = $ctx->emitter->emit($node->returnType, $ctx);
        // --------------------
        // abstract method
        // --------------------
        if (($node->abstract ?? false)) {
            return "{$indent}{$signature}{$phpReturnType};\n\n";
        }

        $code = "{$indent}{$signature}{$phpReturnType}";
        $code .= $ctx->emitter->emit($node->bodyCode, $ctx);
        return $code;
    }
}
