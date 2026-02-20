<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use Exception;
use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\FunctionNode;
use PHireScript\Helper\Debug\Debug;

class FunctionEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof FunctionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $variable = $ctx->emitter->emit($node->variableBase, $ctx);

        $params = [];
        foreach ($node->params->params as $param) {
            $params[] = $ctx->emitter->emit($param, $ctx);
        }

        $params = implode(', ', $params);
        $method = $node->method->phpCodeForConversion;
        $code = str_replace('@self', $variable, $method);
        $code = str_replace('@params', $params, $code);
        $code .= ';';
        return $code;
    }
}
