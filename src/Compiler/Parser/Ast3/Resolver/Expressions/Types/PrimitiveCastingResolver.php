<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\PrimitiveCastingContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\PrimitiveCastingNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class PrimitiveCastingResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isPrimitive() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '(';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {

        $casting = new PrimitiveCastingNode($token, $token->value);

        $parseContext->contextManager->enter(
            new PrimitiveCastingContext($casting)
        );
        $parseContext->definePrevious($casting);

        $context->addChild($casting);
    }
}
