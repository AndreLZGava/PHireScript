<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Root\Interface;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Declarations\Interface\InterfaceBodyContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\InterfaceBodyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class OpeningCurlyBracketResolver implements ContextTokenResolver
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
        $node = new InterfaceBodyNode(
            token: $token,
            bodyOf: $context->node->name,
        );

        $parseContext->contextManager->enter(
            new InterfaceBodyContext($node)
        );
        $context->addChild($node);
    }
}
