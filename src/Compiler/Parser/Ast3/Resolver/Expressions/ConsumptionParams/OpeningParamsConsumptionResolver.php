<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\ConsumptionParams;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Declarations\ParamsConsumptionContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\ParamsNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class OpeningParamsConsumptionResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isOpeningParenthesis() &&
        $parseContext->tokenManager->getPreviousTokenBeforeCurrent()->isIdentifier() &&
        $parseContext->tokenManager->getPreviousToken()->isDot();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $paramsNode = new ParamsNode($token);

        $parseContext->contextManager->enter(
            new ParamsConsumptionContext($paramsNode)
        );
        $context->addChild($paramsNode);
    }
}
