<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\AssignmentContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\BoolNode;
use PHireScript\Compiler\Parser\Ast\CommentNode;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\QueueNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class NumberLiteralResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isNumber();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        if (filter_var($token->value, FILTER_VALIDATE_INT) !== false) {
            $numberNode = new NumberNode($token, filter_var($token->value, FILTER_VALIDATE_INT));
        } elseif (filter_var($token->value, FILTER_VALIDATE_FLOAT) !== false) {
            $numberNode = new NumberNode($token, filter_var($token->value, FILTER_VALIDATE_FLOAT));
        }
        $context->addChild($numberNode);
    }
}
