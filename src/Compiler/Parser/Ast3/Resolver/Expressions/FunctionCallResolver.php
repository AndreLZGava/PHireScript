<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions;

use Exception;
use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\MethodConsumptionContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CommentNode;
use PHireScript\Compiler\Parser\Ast\FunctionNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class FunctionCallResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isIdentifier() &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '(' &&
        $parseContext->symbolTable->from(
            $parseContext->variables->getVariableOnFocus()?->type?->getRawType()
        )->getFunction($token->value);
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $variableType = $parseContext->variables->getVariableOnFocus()->type->getRawType();
        $functionDefinition = $parseContext->symbolTable->from(
            $variableType
        )->getFunction($token->value);

        if (is_null($functionDefinition)) {
            throw new Exception('Method ' . $token->value . ' is not defined for variable of type ' . $variableType);
        }

        $function = new FunctionNode(token: $token);

        $function->method = $functionDefinition;
        $function->variableBase = $parseContext->variables->getVariableOnFocus();

        $parseContext->contextManager->enter(
            new MethodConsumptionContext($function)
        );

        $context->addChild($function);
    }
}
