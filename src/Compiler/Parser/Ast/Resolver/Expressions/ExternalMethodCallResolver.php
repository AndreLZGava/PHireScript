<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class ExternalMethodCallResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isIdentifier()) {
            return false;
        }
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
        if (!$next->isOpeningParenthesis()) {
            return false;
        }
        $focus = $parseContext->variables->getVariableOnFocus();

        // Focus is a LiteralNode representing a class name external alias
        if ($focus instanceof LiteralNode && $parseContext->isExternalAlias($focus->value)) {
            return true;
        }

        // Focus is a variable whose type was inferred as external
        if (
            ($focus instanceof VariableDeclarationNode || $focus instanceof VariableReferenceNode) &&
            $parseContext->isExternalVarType($focus->name)
        ) {
            return true;
        }

        return false;
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();

        // Build a synthetic BaseMethods that passes through the call transparently
        $method = new BaseMethods(
            name:                  $token->value,
            phpCodeForConversion:  '@self',
            returnOfPhpExecution:  [],
            overridesSelfParam:    false,
        );

        $functionNode = new FunctionNode(token: $token);
        $functionNode->method = $method;
        $functionNode->variableBase = $focus;
        $functionNode->isExternalMethodCall = true;
        $functionNode->externalMethodName = $token->value;

        // Replace the LiteralNode placeholder with the FunctionNode as the expression result
        if (!empty($context->children)) {
            array_pop($context->children);
        }

        // Add FunctionNode to the parent context BEFORE entering FunctionCallContext
        $context->addChild($functionNode);

        $parseContext->contextManager->enter(
            new FunctionCallContext($functionNode)
        );
    }
}
