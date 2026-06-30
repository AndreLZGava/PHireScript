<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use Exception;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class FunctionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof FunctionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $insideExpression = $ctx->insideExpression;

        // Safe navigation: variableBase has safeNavigation=true
        $variableBase = $node->variableBase ?? null;
        $isSafeNav = $variableBase instanceof FunctionNode && $variableBase->safeNavigation;

        if ($isSafeNav) {
            return $this->emitSafeNavigation($node, $ctx, $insideExpression);
        }

        $code = $insideExpression ? '' : $this->overrideVariable($node, $ctx);

        $ctx->insideExpression = true;
        $code .= $this->overrideSelf($node, $ctx);
        $ctx->insideExpression = $insideExpression;

        $normalized = $this->normalizeParams(
            $node->params->params,
            $node->method->params,
            $code,
            $ctx
        );
        $code = $this->overrideParams($normalized);

        if (!$insideExpression) {
            $code .= ";\n";
        }

        return $code;
    }

    private function emitSafeNavigation(object $node, EmitContext $ctx, bool $insideExpression): string
    {
        static $chainCounter = 0;
        $tmpVar = '$__chain_' . $chainCounter++;

        // Emit the nullable variableBase (e.g. between()) into a temp var
        $ctx->insideExpression = true;
        $nullableExpr = $ctx->emitter->emit($node->variableBase, $ctx);
        $ctx->insideExpression = $insideExpression;

        // Now emit the current method using the temp var as @self
        $method = $node->method->phpCodeForConversion;
        if (\is_array($method)) {
            $lines = [];
            foreach ($method as $line) {
                $lines[] = \str_replace('@self', $tmpVar, $line);
            }
            $selfCode = $this->wrapAsIIFE($lines, $tmpVar);
        } else {
            $selfCode = \str_replace('@self', $tmpVar, $method);
        }

        $normalized = $this->normalizeParams(
            $node->params->params,
            $node->method->params,
            $selfCode,
            $ctx
        );
        $currentExpr = $this->overrideParams($normalized);

        if ($insideExpression) {
            return "({$tmpVar} = {$nullableExpr}) !== null ? {$currentExpr} : null";
        }

        return "{$tmpVar} = {$nullableExpr};\n{$tmpVar} !== null ? {$currentExpr} : null;\n";
    }

    private function overrideVariable($node, $ctx)
    {
        if ($node->overrideVariableFocus) {
            return "$" . $node->variableBase->name . ' = ';
        }

        return '';
    }

    private function overrideSelf($node, $ctx)
    {
        $variable = $ctx->emitter->emit($node->variableBase, $ctx);
        $method = $node->method->phpCodeForConversion;

        if (\is_array($method)) {
            return $this->emitChainedExpression($method, $variable);
        }

        return \str_replace('@self', $variable, $method);
    }

    private function emitChainedExpression(array $lines, string $self): string
    {
        if (\count($lines) === 1 && \str_starts_with(\ltrim($lines[0]), 'return ')) {
            $expr = \preg_replace('/^\s*return\s+/', '', $lines[0]);
            $expr = \rtrim($expr, '; ');
            return \str_replace('@self', $self, $expr);
        }

        static $counter = 0;
        $tmpVar = '$__chain_' . $counter++;
        $substituted = \array_map(fn($l) => \str_replace('@self', $tmpVar, $l), $lines);
        $indented = \implode("\n    ", $substituted);

        return "({$tmpVar} = {$self}) !== null ? (function() use ({$tmpVar}) {\n    {$indented}\n})() : null";
    }

    private function wrapAsIIFE(array $lines, string $variable): string
    {
        $indented = \implode("\n    ", $lines);

        return "(function() use ($variable) {\n    $indented\n})()";
    }

    private function overrideParams($normalized)
    {
        $params = \implode(', ', $normalized->params);
        $code = \str_replace('@params', $params, $normalized->code);
        return $code;
    }

    private function normalizeParams($sentParams, $expected, $code, $ctx)
    {
        $params = [];

        foreach ($expected as $methodParamId => $expectedParam) {
            if (isset($sentParams[$methodParamId])) {
                $value = $ctx->emitter->emit($sentParams[$methodParamId], $ctx);
            } else {
                $value = $this->processDefaultValue($expectedParam);
            }

            $params[$methodParamId] = $value;
            $expectedParamName = $expectedParam->name === '@params' ? '' : $expectedParam->name;
            $code = $this->processNamedParams($expectedParamName, $value, $code);
        }

        foreach ($sentParams as $methodParamId => $param) {
            if (!isset($expected[$methodParamId])) {
                $params[$methodParamId] = $ctx->emitter->emit($param, $ctx);
            }
        }

        $code = \preg_replace('/@(?!(params)\b)\w+/', '', (string) $code);

        return (object) ['params' => $params, 'code' => $code];
    }

    private function processDefaultValue(BaseParams $param)
    {
        $type = $param->type;

        if (!$param->required && $param->defaultValue === null) {
            return 'null';
        }

        if ($type === 'string') {
            return "'" . $param->defaultValue . "'";
        }

        if ($type === 'bool') {
            return \filter_var($param->defaultValue, FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === 'float') {
            return \filter_var($param->defaultValue, FILTER_VALIDATE_FLOAT);
        }

        if ($type === 'int') {
            return (int) $param->defaultValue;
        }
    }

    private function processNamedParams($paramName, $paramValue, $originalCode)
    {
        if (!\is_int($paramName)) {
            return \str_replace($paramName, (string) $paramValue, $originalCode);
        }
        return '';
    }
}
