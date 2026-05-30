<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ConsumptionParams;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\ParamsConsumptionContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class InstantiationParamsResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isOpeningParenthesis()) {
            return false;
        }
        return $context instanceof FunctionCallContext &&
            $context->node instanceof FunctionNode &&
            $context->node->isExternalInstantiation;
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
