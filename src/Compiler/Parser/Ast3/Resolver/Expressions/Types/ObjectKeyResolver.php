<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\ObjectKeyContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ObjectKeyResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return (
        $token->isStringLiteral() ||
        $token->isIdentifier()) &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->value === ':';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        if ($token->isStringLiteral()) {
            $objectKey = new StringNode($token, $token->value);
        }

        if ($token->isIdentifier()) {
            $objectKey = new StringNode($token, "'" . $token->value . "'");
            $ref = $parseContext->variables->getVariable($token->value);

            if ($ref && $ref?->type?->getRawType() === 'String') {
                $objectKey = new VariableReferenceNode(
                    token: $token,
                    name: $token->value,
                    value: $ref,
                    type: $ref->type,
                );
            }
        }
        $objectKey = new KeyValuePairNode($token, $objectKey, null);
        $parseContext->contextManager->enter(
            new ObjectKeyContext($objectKey)
        );

        $context->addChild($objectKey);
    }
}
