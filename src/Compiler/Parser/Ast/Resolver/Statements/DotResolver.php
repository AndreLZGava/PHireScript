<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class DotResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isDot();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        // When inside a FunctionCallContext, the new focus is the FunctionNode itself
        // (not end(children) which would be a param value)
        if ($context instanceof FunctionCallContext) {
            $parseContext->variables->setVirtualVariable($context->node);
            return;
        }

        $focus = $parseContext->variables->getVariableOnFocus();
        if ($focus !== null) {
            $parseContext->variables->setVirtualVariable($focus);
        }
    }
}
