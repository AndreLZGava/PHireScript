<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Meta\CommentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Collections\QueueNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\StringNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class StringLiteralResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isStringLiteral();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $nodeString = new StringNode($token, $token->value);
        $context->addChild($nodeString);
        $parseContext->variables->setVirtualVariable($nodeString);
    }
}
