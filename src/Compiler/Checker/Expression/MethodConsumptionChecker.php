<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Expression;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Runtime\Exceptions\CompileException;

#[CompilerPass(order: 2)]
class MethodConsumptionChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof FunctionNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        $params = $node->params->params;
        $this->validateRequiredParams($node, $params);
        $this->validateSubTypes($node, $params);

        return;
    }

    private function validateRequiredParams($node, $params)
    {
        $expectedParams = $node->method->params;
        foreach ($expectedParams as $key => $expected) {
            if ($expected->required && !isset($params[$key])) {
                throw new CompileException(
                    'The ' . ($key + 1) . '° parameter (' . $expected->name . ') of type "' . $expected->type .
                        '" is required for method(' . $node->method->name . ') and is missing!',
                    $node->token->line,
                    $node->token->column
                );
            }
        }
    }

    private function validateSubTypes($node, $params)
    {
        $vb = $node->variableBase ?? null;
        $vbType = ($vb !== null && property_exists($vb, 'type')) ? $vb->type : null;
        $type = $vbType?->getRawType() ?? $vb?->getRawType();
        $variableTypes = $vbType?->types ?? ($vbType?->type?->types ?? []);
        $allowedKeys = $vbType?->keys ?? ($vbType?->type?->keys ?? []);
        $expected = $node->method->params;
        foreach ($params as $number => $param) {
            $paramRawType = $param->getRawType();

            if ($expected[$number]->relatedKeyParam && \in_array($paramRawType, $allowedKeys, true)) {
                continue;
            }
            if ($expected[$number]->relatedKeyParam && !\in_array($paramRawType, $allowedKeys, true)) {
                throw new CompileException(
                    'Param of type ' . $paramRawType .
                        ' not allowed for key of ' . $type .
                        '. Allowed in this case ' . \implode('|', $allowedKeys) . '!',
                    $param->token->line,
                    $param->token->column
                );
            }

            if (
                !empty($variableTypes) &&
                !\in_array($paramRawType, $variableTypes, true)
            ) {
                throw new CompileException(
                    'Param of type ' . $paramRawType .
                        ' not allowed for ' . $type .
                        '. Allowed in this case ' . \implode('|', $variableTypes) . '!',
                    $param->token->line,
                    $param->token->column
                );
            }
        }
    }
}
