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
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class VariableAssignmentFactory extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        $current = $parseContext->tokenManager->getCurrentToken();
        $prev = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        return $current->value === '=' && ($prev && $prev->isIdentifier());
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $varName = $parseContext->tokenManager->getPreviousTokenBeforeCurrent()->value;
        $parseContext->tokenManager->advance();

        $expressionTree = $this->parseExpression($parseContext);

        $assignment = new VariableDeclarationNode(
            token: $parseContext->tokenManager->getCurrentToken(),
            name: $varName,
            value: $expressionTree,
            type: 'expression'
        );

        $parseContext->variables->addVariable($assignment);
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
            $ctx->tokenManager->getCurrentToken()->value === '+' ||
            $ctx->tokenManager->getCurrentToken()->value === '-'
        ) {
            $op = $ctx->tokenManager->getCurrentToken()->value;
            $ctx->tokenManager->advance();
            $right = $this->parseMultiplication($ctx);
            $left = new BinaryExpressionNode($left, $op, $right);
        }
        return $left;
    }

    private function parseMultiplication($ctx)
    {
        $left = $this->parsePrimary($ctx);

        while (
            $ctx->tokenManager->getCurrentToken()->value === '*' ||
            $ctx->tokenManager->getCurrentToken()->value === '/'
        ) {
            $op = $ctx->tokenManager->getCurrentToken()->value;
            $ctx->tokenManager->advance();
            $right = $this->parsePrimary($ctx);
            $left = new BinaryExpressionNode($left, $op, $right);
        }
        return $left;
    }

    private function parsePrimary($ctx)
    {
        $token = $ctx->tokenManager->getCurrentToken();

        if ($token->value === '(') {
            $ctx->tokenManager->advance();
            $expr = $this->parseExpression($ctx);
            $ctx->tokenManager->advance();
            return $expr;
        }

        if ($token->isIdentifier()) {
            $var = $ctx->variables->getVariable($token->value);
            if (!$var) {
                throw new \Exception("Variable {$token->value} not defined!");
            }
            $ctx->tokenManager->advance();
            return new VariableReferenceNode(
                token: $token,
                name: $token->value,
                value: $var->name,
                type: null
            );
        }

        // Se for número (T_NUMBER ou similar)
        $ctx->tokenManager->advance();
        return new NumberNode($token, (float) $token->value);
    }
}
