<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Root;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * Like IdentifierResolver but also accepts T_KEYWORD tokens as property names.
 * Needed for properties named after reserved words (e.g. `String type`).
 */
class PropertyNameResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isIdentifier() || $token->isKeyword();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $context->addChild($token->value);
    }
}
