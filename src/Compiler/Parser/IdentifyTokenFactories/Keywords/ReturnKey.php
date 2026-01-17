<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\ReturnNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class ReturnKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $this->tokenManager->advance();
        $expression = $this->parseExpression();

        $returnNode = new ReturnNode($expression);
        $returnNode->line = $this->tokenManager->getCurrentToken()['line'];
        return $returnNode;
    }

    private function parseExpression(): ?Node
    {
        $currentToken = $this->tokenManager->getCurrentToken();
        if (!$currentToken) {
            return null;
        }

        if ($currentToken['value'] === '{') {
            return $this->parseObjectLiteral();
        }

        if ($currentToken['value'] === '[') {
            return $this->parseArrayLiteral();
        }

        if (in_array($currentToken['type'], ['T_STRING_LIT', 'T_NUMBER', 'T_BOOL', 'T_IDENTIFIER'], true)) {
            $value = $currentToken['value'];
            $type = ($currentToken['type'] === 'T_NUMBER') ?
                (
                    str_contains((string) $currentToken['value'], '.') ? 'Float' : 'Int'
                ) : (($currentToken['type'] === 'T_BOOL') ? 'Bool' : 'String');

            $literalNode = new LiteralNode($value, $type);
            $literalNode->line = $this->tokenManager->getCurrentToken()['line'];
            $this->tokenManager->advance();
            return $literalNode;
        }

        if (
            in_array($currentToken['type'], ['T_IDENTIFIER', 'T_TYPE'], true) &&
            in_array($currentToken['value'], ['null', 'Null', 'Void', 'void'], true)
        ) {
            $value = $currentToken['value'];
            $type = 'Null';

            $literalNode = new LiteralNode($value, $type);
            $literalNode->line = $this->tokenManager->getCurrentToken()['line'];
            $this->tokenManager->advance();
            return $literalNode;
        }
        //Debug::show($currentToken);exit;
        return null;
    }
    private function parseObjectLiteral(): ObjectLiteralNode
    {
        $this->tokenManager->advance();
        $properties = [];

        while (
            $this->tokenManager->getCurrentToken() &&
            $this->tokenManager->getCurrentToken()['value'] !== '}'
        ) {
            $keyNode = $this->parseExpression();
            $currentToken = $this->tokenManager->getCurrentToken();
            if ($currentToken && $currentToken['value'] === ':') {
                $this->tokenManager->advance();
                $valueNode = $this->parseExpression();

                $properties[] = new KeyValuePairNode($keyNode, $valueNode);
            }

            if ($currentToken && $currentToken['value'] === ',') {
                $this->tokenManager->advance();
            } elseif ($currentToken && $currentToken['value'] !== '}') {
                break;
            }
        }
        $objectNode = new ObjectLiteralNode($properties);

        if ($this->tokenManager->getCurrentToken()) {
            $objectNode->line = $this->tokenManager->getCurrentToken()['line'];
            $this->tokenManager->advance();
        }

        return $objectNode;
    }

    private function parseArrayLiteral(): ArrayLiteralNode
    {
        $this->tokenManager->advance(); // Pula o '['
        $elements = [];

        while ($this->tokenManager->getCurrentToken() && $this->tokenManager->getCurrentToken()['value'] !== ']') {
            $expression = $this->parseExpression();

            if ($this->tokenManager->getCurrentToken() && $this->tokenManager->getCurrentToken()['value'] === '=>') {
                $this->tokenManager->advance(); // Pula '=>'
                $value = $this->parseExpression();

                $elements[] = new KeyValuePairNode($expression, $value);
            } else {
                $elements[] = $expression;
            }

            if ($this->tokenManager->getCurrentToken() && $this->tokenManager->getCurrentToken()['value'] === ',') {
                $this->tokenManager->advance();
            } else {
                if ($this->tokenManager->getCurrentToken()['value'] !== ']') {
                    break;
                }
            }
        }

        $arrayLiteralNode = new ArrayLiteralNode($elements);

        if ($this->tokenManager->getCurrentToken()) {
            $arrayLiteralNode->line = $this->tokenManager->getCurrentToken()['line'];
            $this->tokenManager->advance();
        }

        return $arrayLiteralNode;
    }
}
