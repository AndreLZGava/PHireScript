<?php

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\BinaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Literal\FloatLiteral;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactoryInterface;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class VariableAssignmentFactory extends GlobalFactory
{
    public function isTheCase()
    {
        $current = $this->tokenManager->getCurrentToken();
        $prev = $this->tokenManager->getPreviousTokenBeforeCurrent();
        return $current->value === '=' && ($prev && $prev->isIdentifier());
    }

    public function process(Program $program): ?Node
    {
        $varName = $this->tokenManager->getPreviousTokenBeforeCurrent()->value;
        $this->tokenManager->advance();

        $expressionTree = $this->parseExpression($this->parseContext);

        $assignment = new VariableDeclarationNode(
            token: $this->tokenManager->getCurrentToken(),
            name: $varName,
            value: $expressionTree,
            type: 'expression'
        );

        $this->parseContext->variables->addVariable($assignment);
        return $assignment;
    }

    private function parseExpression(ParseContext $ctx)
    {
        return $this->parseAddition($ctx);
    }

    private function parseAddition($ctx)
    {
        $left = $this->parseMultiplication($ctx);

        while (
            $this->tokenManager->getCurrentToken()->value === '+' ||
            $this->tokenManager->getCurrentToken()->value === '-'
        ) {
            $op = $this->tokenManager->getCurrentToken()->value;
            $this->tokenManager->advance();
            $right = $this->parseMultiplication($ctx);
            $left = new BinaryExpressionNode($left, $op, $right);
        }
        return $left;
    }

    private function parseMultiplication($ctx)
    {
        $left = $this->parsePrimary($ctx);

        while (
            $this->tokenManager->getCurrentToken()->value === '*' ||
            $this->tokenManager->getCurrentToken()->value === '/'
        ) {
            $op = $this->tokenManager->getCurrentToken()->value;
            $this->tokenManager->advance();
            $right = $this->parsePrimary($ctx);
            $left = new BinaryExpressionNode($left, $op, $right);
        }
        return $left;
    }

    private function parsePrimary($ctx)
    {
        $token = $this->tokenManager->getCurrentToken();

        if ($token->value === '(') {
            $this->tokenManager->advance();
            $expr = $this->parseExpression($ctx);
            $this->tokenManager->advance();
            return $expr;
        }

        if ($token->isIdentifier()) {
            $var = $ctx->variables->getVariable($token->value);
            if (!$var) {
                throw new \Exception("Variable {$token->value} not defined!");
            }
            $this->tokenManager->advance();
            return new VariableReferenceNode(
                token: $token,
                name: $token->value,
                value: $var->name,
                type: null
            );
        }

      // Se for número (T_NUMBER ou similar)
        $this->tokenManager->advance();
        return new NumberNode($token, (float) $token->value);
    }
}
