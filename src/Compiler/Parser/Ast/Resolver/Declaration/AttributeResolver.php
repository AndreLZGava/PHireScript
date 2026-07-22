<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\AttributeContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class AttributeResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === 'attribute';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new AttributeNode(token: $token);

        $parseContext->contextManager->enter(
            new AttributeContext($node)
        );

        $context->addChild($node);
    }
}
