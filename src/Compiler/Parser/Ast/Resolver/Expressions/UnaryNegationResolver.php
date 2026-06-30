<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\UnaryExpressionContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\UnaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class UnaryNegationResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if ($token->value !== '!' && $token->value !== '-') {
            return false;
        }

        // Unary: at the start of an expression (no children yet) or after an operator node
        return empty($context->children) && $parseContext->peekPrevious() === null;
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new UnaryExpressionNode($token, $token->value);
        $parseContext->contextManager->enter(new UnaryExpressionContext($node));
    }
}
