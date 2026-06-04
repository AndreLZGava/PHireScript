<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ConsumptionParams;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\ParamsConsumptionContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class OpeningParamsConsumptionResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        $prevId = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        $prevDot = $parseContext->tokenManager->getPreviousToken();
        return $token->isOpeningParenthesis() &&
            $prevId->isIdentifier() &&
            ($prevDot->isDot() || $prevDot->isSafeNavigation());
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
