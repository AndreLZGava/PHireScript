<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Scopes;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Scopes\ElseIfScopeContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\ElseIfScopeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ElseIfScopeResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isOpeningCurlyBracket();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new ElseIfScopeNode(token: $token);

        $parseContext->contextManager->enter(
            new ElseIfScopeContext($node)
        );

        $context->addChild($node);
    }
}
