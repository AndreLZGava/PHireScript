<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\VariableDeclarationContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Meta\CommentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class VariableResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isIdentifier()
        && $parseContext->tokenManager->getNextTokenAfterCurrent()->value == '=';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $existing = $parseContext->variables->getVariable($token->value);

        $variable = new VariableDeclarationNode(
            token: $token,
            name: $token->value,
            type: $existing?->type,
        );

        $parseContext->definePrevious($variable);
        $parseContext->variables->addVariable($variable);
    }
}
