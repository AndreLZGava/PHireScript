<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

trait DataArrayObjectModelingTrait
{
    private function parseExpression(ParseContext $parseContext): ?Node
    {
        $currentToken = $parseContext->tokenManager->getCurrentToken();
        $nextToken = $parseContext->tokenManager->getNextTokenAfterCurrent();
        if (!$currentToken) {
            return null;
        }

        if ($currentToken->value === '{') {
            return $this->parseObjectLiteral($parseContext);
        }

        if ($currentToken->value === '[') {
            return $this->parseArrayLiteral($parseContext);
        }

        if (in_array($currentToken->type, ['T_STRING_LIT', 'T_NUMBER', 'T_BOOL', 'T_IDENTIFIER'], true)) {
            $value = $currentToken->value;
            $type = ($currentToken->isNumber()) ?
                (
                    str_contains((string) $currentToken->value, '.') ? 'Float' : 'Int'
                ) : (($currentToken->isBool()) ? 'Bool' : ($nextToken->value === ':' ? 'Property' : 'String')
                );
            $literalNode = new LiteralNode($parseContext->tokenManager->getCurrentToken(), $value, $type);
            $parseContext->tokenManager->advance();
            return $literalNode;
        }

        if (
            in_array($currentToken->type, ['T_IDENTIFIER', 'T_TYPE'], true) &&
            in_array($currentToken->value, ['null', 'Null', 'Void', 'void'], true)
        ) {
            $value = $currentToken->value;
            $type = 'Null';

            $literalNode = new LiteralNode($parseContext->tokenManager->getCurrentToken(), $value, $type);
            $parseContext->tokenManager->advance();
            return $literalNode;
        }
        //Debug::show($currentToken);exit;
        return null;
    }

    private function parseObjectLiteral($parseContext): ObjectLiteralNode
    {
        $parseContext->tokenManager->advance();
        $properties = [];

        while (
            $parseContext->tokenManager->getCurrentToken() &&
            $parseContext->tokenManager->getCurrentToken()->value !== '}'
        ) {
            $keyNode = $this->parseExpression($parseContext);
            $currentToken = $parseContext->tokenManager->getCurrentToken();
            if ($currentToken && $currentToken->value === ':') {
                $parseContext->tokenManager->advance();
                $valueNode = $this->parseExpression($parseContext);

                $properties[] = new KeyValuePairNode($currentToken, $keyNode, $valueNode);
            }

            if ($currentToken && $currentToken->value === ',') {
                $parseContext->tokenManager->advance();
            } elseif ($currentToken && $currentToken->value !== '}') {
                break;
            }
        }
        $objectNode = new ObjectLiteralNode($parseContext->tokenManager->getCurrentToken(), $properties);

        if ($parseContext->context->getCurrentContext() === Context::Casting) {
            $parseContext->tokenManager->advance();
        }

        return $objectNode;
    }

    private function parseArrayLiteral($parseContext): ArrayLiteralNode
    {
        $parseContext->tokenManager->advance(); // Pula o '['
        $elements = [];

        while (
            $parseContext->tokenManager->getCurrentToken() &&
            $parseContext->tokenManager->getCurrentToken()->value !== ']'
        ) {
            $expression = $this->parseExpression($parseContext);

            if (
                $parseContext->tokenManager->getCurrentToken() &&
                $parseContext->tokenManager->getCurrentToken()->value === '=>'
            ) {
                $parseContext->tokenManager->advance(); // Pula '=>'
                $value = $this->parseExpression($parseContext);

                $elements[] = new KeyValuePairNode(
                    $parseContext->tokenManager->getCurrentToken(),
                    $expression,
                    $value
                );
            } else {
                $elements[] = $expression;
            }

            if (
                $parseContext->tokenManager->getCurrentToken() &&
                $parseContext->tokenManager->getCurrentToken()->value === ','
            ) {
                $parseContext->tokenManager->advance();
            } else {
                if ($parseContext->tokenManager->getCurrentToken()->value !== ']') {
                    break;
                }
            }
        }

        $arrayLiteralNode = new ArrayLiteralNode($parseContext->tokenManager->getCurrentToken(), $elements);
        if (
            $parseContext->context->getCurrentContext() === Context::Casting
        ) {
            $parseContext->context->exitContext();
            $parseContext->tokenManager->advance();
        }

        if (
            $parseContext->context->getCurrentContext() === Context::Assignment
        ) {
            $parseContext->tokenManager->advance();
        }
        return $arrayLiteralNode;
    }
}
