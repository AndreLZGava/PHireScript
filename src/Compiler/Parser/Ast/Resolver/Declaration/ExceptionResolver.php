<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\ExceptionContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExceptionNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ExceptionResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === 'exception';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new ExceptionNode(token: $token);

        $parseContext->contextManager->enter(
            new ExceptionContext($node)
        );

        $context->addChild($node);
    }
}
