<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use Exception;
use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\FunctionNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class FunctionEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof FunctionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {

        $code = $this->overrideSelf($node, $ctx);
        $normalized = $this->normalizeParams(
            $node->params->params,
            $node->method->params,
            $code,
            $ctx
        );

        $code = $this->overrideParams($normalized);
        if ($node->method->child === null) {
            $code .= ';';
        }
        return $code;
    }

    private function overrideSelf($node, $ctx)
    {
        $variable = $ctx->emitter->emit($node->variableBase, $ctx);

        $method = $node->method->phpCodeForConversion;
        return str_replace('@self', $variable, $method);
    }

    private function overrideParams($normalized)
    {
        $params = implode(', ', $normalized->params);
        $code = str_replace('@params', $params, $normalized->code);
        return $code;
    }

    private function normalizeParams($sentParams, $expected, $code, $ctx)
    {
        $params = [];
        $last = end($expected) ?: (object) ['name' => ''];
        foreach ($sentParams as $methodParamId => $param) {
            if (isset($sentParams[$methodParamId])) {
                $params[$methodParamId] = $ctx->emitter->emit($sentParams[$methodParamId], $ctx);
                $expectedParam = isset($expected[$methodParamId]) ? $expected[$methodParamId] : $last;
                $expectedParamName = $expectedParam->name === '@params' ? '' : $expectedParam->name;
                $code = $this->processNamedParams($expectedParamName, $params[$methodParamId], $code);
                continue;
            }
            $code = $this->processNamedParams($param->name, $this->processDefaultValue($param), $code);
        }

        return (object) ['params' => $params, 'code' => $code];
    }

    private function processDefaultValue(BaseParams $param)
    {
        $type = $param->type;

        if (!$param->required && $param->defaultValue === null) {
            return '';
        }

        if ($type === 'string') {
            return "'" . $param->defaultValue . "'";
        }

        if ($type === 'bool') {
            return filter_var($param->defaultValue, FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === 'float') {
            return filter_var($param->defaultValue, FILTER_VALIDATE_FLOAT);
        }

        if ($type === 'int') {
            return (int) $param->defaultValue;
        }
    }

    private function processNamedParams($paramName, $paramValue, $originalCode)
    {
        if (gettype($paramName) !== 'integer') {
            return str_replace($paramName, $paramValue, $originalCode);
        }
        return '';
    }
}
