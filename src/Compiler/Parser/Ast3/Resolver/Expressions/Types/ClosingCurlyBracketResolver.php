<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ClosingCurlyBracketResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isClosingCurlyBracket();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
    }
}
