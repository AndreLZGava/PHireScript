<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

class FunctionCallNotFoundResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isIdentifier() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isOpeningParenthesis();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();
        $rawType = null;
        if ($focus !== null) {
            if (method_exists($focus, 'getRawType')) {
                $rawType = $focus->getRawType();
            } elseif (
                property_exists($focus, 'type') &&
                $focus->type !== null &&
                method_exists($focus->type, 'getRawType')
            ) {
                $rawType = $focus->type->getRawType();
            }
        }
        $typeHint = $rawType !== null ? " on type \"{$rawType}\"" : '';
        throw new CompileException(
            "Method \"{$token->value}\" does not exist nor is supported{$typeHint}",
            $token->line,
            $token->column
        );
    }
}
