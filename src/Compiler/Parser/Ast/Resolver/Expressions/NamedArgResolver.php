<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\NamedArgContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NamedArgNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class NamedArgResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isIdentifier()
            && $parseContext->tokenManager->getNextTokenAfterCurrent()->isColon();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context,
    ): void {
        $node = new NamedArgNode($token, $token->value);
        $parseContext->contextManager->enter(new NamedArgContext($node));
        $context->addChild($node);
    }
}
