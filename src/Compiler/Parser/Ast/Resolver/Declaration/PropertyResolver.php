<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\PropertyDeclarationContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\PropertyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class PropertyResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isType()
            //@todo At one point there will be identifier followed by identifier
            // because in this case the expected is that the first identifier
            // represents a external or a use case.
            && $parseContext->tokenManager->getNextTokenAfterCurrent()->isIdentifier();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $modifiers = $this->handleModifiers($parseContext->consumePrevious());
        $property = new PropertyNode(
            token: $token,
            types: [$token->value],
            modifiers: $modifiers,
        );

        $parseContext->contextManager->enter(
            new PropertyDeclarationContext($property)
        );

        $parseContext->definePrevious($property);
        $parseContext->variables->addProperty($property);
        $context->addChild($property);
    }

    private function handleModifiers($previousModifiers)
    {
        $modifiers = $previousModifiers ? ModifiersResolver::getModifiers($previousModifiers) : [];
        return $modifiers ?? [];
    }
}
