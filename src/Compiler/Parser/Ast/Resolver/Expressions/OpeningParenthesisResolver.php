<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\GroupedExpressionContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\GroupedExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class OpeningParenthesisResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isOpeningParenthesis()) {
            return false;
        }

        // Do not intercept arrow function params: (Type param) => ...
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
        if ($next->isPrimitive() || $next->isSuperType() || $next->isMetaType()) {
            return false;
        }

        // Do not intercept if immediately after a closing paren (e.g. chained calls)
        // or if context has no children yet and next is a type keyword
        return true;
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new GroupedExpressionNode($token);
        $parseContext->contextManager->enter(new GroupedExpressionContext($node));
    }
}
