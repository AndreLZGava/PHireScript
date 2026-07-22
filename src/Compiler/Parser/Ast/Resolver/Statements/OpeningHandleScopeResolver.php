<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Scopes\HandleScopeContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\HandleNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class OpeningHandleScopeResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isOpeningCurlyBracket();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $node = new HandleNode(
            token: $token,
        );

        $parseContext->contextManager->enter(
            new HandleScopeContext($node)
        );
        $context->addChild($node);

        // Register the catch variable (e.g. `e` in `handle AppException e`) as a variable
        // so it can be referenced inside the handle body (e.g. `cause: e`).
        $handleNode = $context->node ?? null;
        if ($handleNode instanceof HandleNode && $handleNode->param !== null) {
            foreach ($handleNode->param->params as $param) {
                $name = \is_string($param->name) ? $param->name : null;
                if ($name !== null) {
                    $parseContext->variables->addVariable(
                        new VariableDeclarationNode($token, $name)
                    );
                }
            }
        }
    }
}
