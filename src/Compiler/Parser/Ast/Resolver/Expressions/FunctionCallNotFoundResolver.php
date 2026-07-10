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
        $varName = null;

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

            if (property_exists($focus, 'name')) {
                $varName = $focus->name;
            }
        }

        $parts = [];

        if ($varName !== null) {
            $parts[] = "Variable: \${$varName}";
        }

        if ($rawType !== null) {
            $parts[] = "Type: {$rawType}";
        }

        $detail = !empty($parts) ? ' — ' . \implode(' | ', $parts) : '';

        throw new CompileException(
            "Method \"{$token->value}\" does not exist nor is supported{$detail}",
            $token->line,
            $token->column
        );
    }
}
