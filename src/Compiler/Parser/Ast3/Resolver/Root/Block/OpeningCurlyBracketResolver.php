<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Root\Block;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Declarations\ClassBody\ClassBodyContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\PrimitiveCastingContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\ClassBodyNode;
use PHireScript\Compiler\Parser\Ast\PrimitiveCastingNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class OpeningCurlyBracketResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === '{';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new ClassBodyNode(
            token: $token,
            bodyOf: $context->node->name,
        );

        $parseContext->contextManager->enter(
            new ClassBodyContext($node)
        );
        $context->addChild($node);
    }
}
