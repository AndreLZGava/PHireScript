<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use Exception;
use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Helper\Debug\Debug;

class MethodEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof MethodDefinition;
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
        $returnType = $node->returnType;
        $phpReturnType = '';

        if (is_array($returnType)) {
            $phpReturnType = ': array';
        } elseif (is_string($returnType) && str_starts_with($returnType, '[')) {
            $phpReturnType = ': array';
        } elseif (!empty($returnType)) {
            $phpReturnType = ': ' . strtolower($returnType);
        }

        // --------------------
        // abstract method
        // --------------------
        if (($ctx->insideInterface ?? false) || ($node->abstract ?? false)) {
            return "{$indent}{$signature}{$phpReturnType};\n\n";
        }

        // --------------------
        // BODY (context magic 🔥)
        // --------------------
        $previousReturnType = $ctx->currentMethodReturnType;
        $ctx->currentMethodReturnType = $node->returnType;

        $code = "{$indent}{$signature}{$phpReturnType} {\n";

        foreach ($node->bodyCode ?? [] as $stmt) {
            $code .= $indent . $indent . $ctx->emitter->emit($stmt, $ctx) . "\n";
        }

        $code .= "{$indent}}\n\n";

        // restore context
        $ctx->currentMethodReturnType = $previousReturnType;

        return $code;
    }
}
