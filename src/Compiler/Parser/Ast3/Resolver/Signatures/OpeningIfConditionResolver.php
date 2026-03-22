<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Signatures;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Scopes\IfConditionContext;
use PHireScript\Compiler\Parser\Ast3\Context\Signatures\ParameterListContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\IfConditionNode;
use PHireScript\Compiler\Parser\Ast\ParamsListNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class OpeningIfConditionResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isOpeningParenthesis();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $idCondition = new IfConditionNode($token);

        $parseContext->contextManager->enter(
            new IfConditionContext($idCondition)
        );
        $context->addChild($idCondition);
    }
}
