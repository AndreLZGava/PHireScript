<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CommentNode;
use PHireScript\Compiler\Parser\Ast\NullNode;
use PHireScript\Compiler\Parser\Ast\QueueNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class NullLiteralResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isNull();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $nodeString = new NullNode($token);
        $context->addChild($nodeString);
    }
}
