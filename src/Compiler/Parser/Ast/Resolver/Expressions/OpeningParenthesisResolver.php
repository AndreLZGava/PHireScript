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

        // Do not intercept zero-param arrow function: (): ReturnType => ...
        if ($next->isClosingParenthesis()) {
            $isArrow = $parseContext->tokenManager->sequence()
                ->lookAhead()
                ->once(fn ($t) => $t->isOpeningParenthesis())
                ->once(fn ($t) => $t->isClosingParenthesis())
                ->once(fn ($t) => $t->isColon())
                ->skipUntil(fn ($t) => $t->isArrow())
                ->once(fn ($t) => $t->isArrow())
                ->match();
            if ($isArrow) {
                return false;
            }
        }

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
