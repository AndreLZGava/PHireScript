<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Expression;

use PHireScript\Compiler\Parser\Ast2\Expressions;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class Assignment extends Expressions
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {

        return $token->isSymbol() &&
        $token->value === '=';
    }
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $left = $parseContext->context->getCurrentContextElement();

        $assignment = new AssignmentNode(token: $token, left: $left);

        $parseContext->context->enterContext(Context::Assignment, $assignment);
        return null;
    }
}
