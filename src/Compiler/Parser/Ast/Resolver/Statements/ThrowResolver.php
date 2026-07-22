<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Statements\ThrowContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ThrowStatementNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ThrowResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === 'throw';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new ThrowStatementNode(token: $token, exceptionExpression: null);

        $parseContext->contextManager->enter(
            new ThrowContext($node, $token)
        );

        $context->addChild($node);
    }
}
