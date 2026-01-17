<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
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

        if ($currentToken['value'] === '[') {
            return $this->parseArrayLiteral();
        }

        if (in_array($currentToken['type'], ['T_STRING_LIT', 'T_NUMBER', 'T_BOOL'], true)) {
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

    private function parseArrayLiteral(): ArrayLiteralNode
    {
        $this->tokenManager->advance();
        $elements = [];
        while (
            $this->tokenManager->getCurrentToken() &&
            $this->tokenManager->getCurrentToken()['value'] !== ']' ||
            $this->tokenManager->isEndOfTokens()
        ) {
            $elements[] = $this->parseExpression();

            if ($this->tokenManager->getCurrentToken() && $this->tokenManager->getCurrentToken()['value'] === ',') {
                $this->tokenManager->advance();
            }
        }

        $arrayLiteralNode = new ArrayLiteralNode($elements);
        $arrayLiteralNode->line = $this->tokenManager->getCurrentToken()['line'];
        $this->tokenManager->advance();
        return $arrayLiteralNode;
    }
}
