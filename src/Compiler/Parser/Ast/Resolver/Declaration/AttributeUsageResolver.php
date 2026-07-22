<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\AttributeUsageContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeUsageNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class AttributeUsageResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === '@';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new AttributeUsageNode(token: $token, name: '');

        $parseContext->contextManager->enter(
            new AttributeUsageContext($node)
        );
    }
}
