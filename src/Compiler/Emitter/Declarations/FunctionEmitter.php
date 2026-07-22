<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NamedArgNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ThisExpressionNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;
use PHireScript\Runtime\Exceptions\CompileException;

class FunctionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof FunctionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $insideExpression = $ctx->insideExpression;

        // User-defined method call: this.myMethod() — no BaseMethods attached
        if (!isset($node->method) && ($node->variableBase ?? null) instanceof ThisExpressionNode) {
            $params = $this->emitUserMethodParams($node, $ctx);
            $call   = '$this->' . $node->token->value . '(' . $params . ')';
            return $insideExpression ? $call : $call . ";\n";
        }

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
        $method = $this->qualifyInternalClasses($node->method->phpCodeForConversion, $ctx);
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
        $method = $this->qualifyInternalClasses($node->method->phpCodeForConversion, $ctx);

        if (\is_array($method)) {
            return $this->emitChainedExpression($method, $variable);
        }

        return \str_replace('@self', $variable, $method);
    }

    /**
     * Replaces bare Internal class names (e.g. ArrayFunctions::) with their
     * fully-qualified counterpart (e.g. PHireScript\Sandbox\Internal\Types\ArrayFunctions::).
     */
    private function qualifyInternalClasses(string|array $code, $ctx): string|array
    {
        if (empty($ctx->internalTypeClasses)) {
            return $code;
        }

        $replace = static function (string $line) use ($ctx): string {
            foreach ($ctx->internalTypeClasses as $shortName => $fqcn) {
                $line = preg_replace(
                    '/(?<!\\\\)\b' . preg_quote($shortName, '/') . '::/',
                    '\\' . $fqcn . '::',
                    $line
                ) ?? $line;
            }
            return $line;
        };

        if (\is_array($code)) {
            return array_map($replace, $code);
        }

        return $replace($code);
    }

    private function emitChainedExpression(array $lines, string $self): string
    {
        if (\count($lines) === 1 && \str_starts_with(\ltrim((string) $lines[0]), 'return ')) {
            $expr = \preg_replace('/^\s*return\s+/', '', (string) $lines[0]);
            $expr = \rtrim((string) $expr, '; ');
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

    private function emitUserMethodParams(FunctionNode $node, EmitContext $ctx): string
    {
        $params = $node->params->params ?? [];
        if (empty($params)) {
            return '';
        }
        $parts = [];
        foreach ($params as $param) {
            $parts[] = $ctx->emitter->emit($param, $ctx);
        }
        return \implode(', ', $parts);
    }

    private function overrideParams($normalized)
    {
        $params = \implode(', ', $normalized->params);
        $code = \str_replace('@params', $params, $normalized->code);
        return $code;
    }

    private function normalizeParams($sentParams, $expected, $code, $ctx)
    {
        $sentParams ??= [];

        $hasNamed = false;
        $hasPositional = false;
        foreach ($sentParams as $param) {
            if ($param instanceof NamedArgNode) {
                $hasNamed = true;
            } else {
                $hasPositional = true;
            }
        }

        if ($hasNamed && $hasPositional) {
            $token = \current($sentParams)->token ?? null;
            throw new CompileException(
                'Cannot mix positional and named arguments in the same call',
                $token?->line ?? 0,
                $token?->column ?? 0,
            );
        }

        if ($hasNamed) {
            return $this->normalizeNamedParams($sentParams, $expected, $code, $ctx);
        }

        $params = [];

        foreach ($expected as $methodParamId => $expectedParam) {
            if (isset($sentParams[$methodParamId])) {
                $value = $ctx->emitter->emit($sentParams[$methodParamId], $ctx);
            } else {
                if ($expectedParam->relatedKeyParam && !$expectedParam->required) {
                    $code = \str_replace('[' . $expectedParam->name . ']', '[]', $code);
                    continue;
                }
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

    private function normalizeNamedParams(array $sentParams, array $expected, string $code, $ctx): object
    {
        $sentMap = [];
        foreach ($sentParams as $namedArg) {
            $name = $namedArg->paramName;
            if (isset($sentMap[$name])) {
                throw new CompileException(
                    "Duplicate named argument: '{$name}'",
                    $namedArg->token->line,
                    $namedArg->token->column,
                );
            }
            $sentMap[$name] = $namedArg;
        }

        $expectedNames = [];
        foreach ($expected as $expectedParam) {
            $expectedNames[] = \ltrim((string) $expectedParam->name, '@');
        }

        foreach (\array_keys($sentMap) as $sentName) {
            if (!\in_array($sentName, $expectedNames, true)) {
                $namedArg = $sentMap[$sentName];
                throw new CompileException(
                    "Unknown named argument: '{$sentName}'",
                    $namedArg->token->line,
                    $namedArg->token->column,
                );
            }
        }

        $params = [];
        foreach ($expected as $methodParamId => $expectedParam) {
            $normalizedName = \ltrim((string) $expectedParam->name, '@');

            if (isset($sentMap[$normalizedName])) {
                $value = $ctx->emitter->emit($sentMap[$normalizedName]->value, $ctx);
            } elseif ($expectedParam->required) {
                throw new CompileException(
                    "Missing required named argument: '{$normalizedName}'",
                    0,
                    0,
                );
            } else {
                if ($expectedParam->relatedKeyParam) {
                    $code = \str_replace('[' . $expectedParam->name . ']', '[]', $code);
                    continue;
                }
                $value = $this->processDefaultValue($expectedParam);
            }

            $params[$methodParamId] = $value;
            $expectedParamName = $expectedParam->name === '@params' ? '' : $expectedParam->name;
            $code = $this->processNamedParams($expectedParamName, $value, $code);
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
