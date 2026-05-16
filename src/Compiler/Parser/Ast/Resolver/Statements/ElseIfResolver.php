<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Statements\ElseIfContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ElseIfNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\IfNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ElseIfResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->value === 'elseif';
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new ElseIfNode(token: $token);

        /** @var IfNode $ifNode */
        $ifNode = $context->node;
        $ifNode->elseIfClauses[] = $node;

        $parseContext->contextManager->enter(
            new ElseIfContext($node)
        );
    }
}
