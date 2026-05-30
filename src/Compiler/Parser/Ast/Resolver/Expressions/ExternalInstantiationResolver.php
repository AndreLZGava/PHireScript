<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class ExternalInstantiationResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isOpeningParenthesis()) {
            return false;
        }
        $focus = $parseContext->variables->getVariableOnFocus();
        return $focus instanceof LiteralNode && $parseContext->isExternalAlias($focus->value);
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();

        $method = new BaseMethods(
            name:                 '__construct',
            phpCodeForConversion: '@self',
            returnOfPhpExecution: [],
            overridesSelfParam:   false,
        );

        $functionNode = new FunctionNode(token: $token);
        $functionNode->method = $method;
        $functionNode->variableBase = $focus;
        $functionNode->isExternalInstantiation = true;

        // Replace the LiteralNode placeholder with the FunctionNode
        if (!empty($context->children)) {
            array_pop($context->children);
        }

        $context->addChild($functionNode);

        $parseContext->contextManager->enter(
            new FunctionCallContext($functionNode)
        );
    }
}
