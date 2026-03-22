<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\ArrayKeyContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CommentNode;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\QueueNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class ArrayKeyResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return (
        $token->isStringLiteral() ||
        $token->isNumber() ||
        ($token->isIdentifier() && $parseContext->variables->getVariable($token->value))
        ) &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->isColon();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        if ($token->isStringLiteral()) {
            $arrayKey = new StringNode($token, $token->value);
        }

        if ($token->isNumber()) {
            $arrayKey = new NumberNode($token, (int) $token->value);
        }

        if ($token->isIdentifier()) {
            $ref = $parseContext->variables->getVariable($token->value);
            $arrayKey = new VariableReferenceNode(
                token: $token,
                name: $token->value,
                value: $ref,
                type: $ref->type,
            );
        }

        $arrayKey = new KeyValuePairNode($token, $arrayKey, null);
        $parseContext->contextManager->enter(
            new ArrayKeyContext($arrayKey)
        );

        $context->addChild($arrayKey);
    }
}
