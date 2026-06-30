<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\BinaryExpressionContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BinaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class BinaryExpressionResolver implements ContextTokenResolver
{
    private const OPERATORS = [
        '+', '-', '*', '/', '%', '**',
        '>', '<', '==', '===', '!=', '!==', '>=', '<=',
        '&&', '||',
    ];

    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return in_array($token->value, self::OPERATORS, true)
            && ($parseContext->peekPrevious() !== null || !empty($context->children));
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $left = $parseContext->peekPrevious() !== null
            ? $parseContext->consumePrevious()
            : array_pop($context->children);

        $node = new BinaryExpressionNode($left, $token->value, null);

        $parseContext->contextManager->enter(new BinaryExpressionContext($node));
    }
}
