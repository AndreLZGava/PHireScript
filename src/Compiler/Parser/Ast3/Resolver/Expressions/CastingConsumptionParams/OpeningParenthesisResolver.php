<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CastingConsumptionParams;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Declarations\ParamsConsumptionContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CommentNode;
use PHireScript\Compiler\Parser\Ast\ParamsNode;
use PHireScript\Compiler\Parser\Ast\QueueNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class OpeningParenthesisResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        $beforeCurrent = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        return $token->value === '(' &&
            $beforeCurrent->isPrimitive() || $beforeCurrent->isSuperType() || $beforeCurrent->isMetaType();
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
