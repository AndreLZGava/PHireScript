<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ThisExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ThisPropertyAccessResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isIdentifier()) {
            return false;
        }

        $focus = $parseContext->variables->getVariableOnFocus();

        return $focus instanceof ThisExpressionNode
            && !$parseContext->tokenManager->getNextTokenAfterCurrent()->isOpeningParenthesis();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();
        $propertyAccess = new PropertyAccessNode($token, $focus, $token->value);

        $prop = $parseContext->variables->getProperty($token->value);
        if ($prop !== null && !empty($prop->types)) {
            $propertyAccess->resolvedType = $prop->types[0];
        }

        $parseContext->variables->setVirtualVariable($propertyAccess);

        if (!empty($context->children) && end($context->children) instanceof ThisExpressionNode) {
            array_pop($context->children);
        }

        $nextToken = $parseContext->tokenManager->getNextTokenAfterCurrent();
        $nextIsAssignment = $nextToken->value === '=';

        if ($nextIsAssignment) {
            $parseContext->definePrevious($propertyAccess);
        } else {
            $context->addChild($propertyAccess);
        }
    }
}
