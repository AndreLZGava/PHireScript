<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class SafeNavigationResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isSafeNavigation();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        // When inside a FunctionCallContext, the new focus is the FunctionNode itself
        if ($context instanceof FunctionCallContext) {
            $node = $context->node;
            $node->safeNavigation = true;
            $parseContext->variables->setVirtualVariable($node);
            return;
        }

        $last = !empty($context->children) ? end($context->children) : null;
        if ($last !== null) {
            $parseContext->variables->setVirtualVariable($last);
        }
    }
}
