<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * Resolves `ExternalAlias.MEMBER` and `ExternalAlias.CONSTANT` access
 * (e.g. DateTimePhp.ATOM, DateTimePhp.format) within any context where an
 * external LiteralNode is on focus and the current token is the member name.
 *
 * Must be placed AFTER ExternalClassAccessResolver and DotResolver (which consumes the dot).
 */
class ExternalPropertyAccessResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isIdentifier() && !$token->isGlobalConst()) {
            return false;
        }
        $focus = $parseContext->variables->getVariableOnFocus();
        return $focus instanceof LiteralNode && $parseContext->isExternalAlias($focus->value);
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();
        $propertyAccess = new PropertyAccessNode($token, $focus, $token->value);
        $parseContext->variables->setVirtualVariable($propertyAccess);

        // Replace the LiteralNode placeholder with the PropertyAccessNode
        if (!empty($context->children)) {
            array_pop($context->children);
        }
        $context->addChild($propertyAccess);
    }
}
