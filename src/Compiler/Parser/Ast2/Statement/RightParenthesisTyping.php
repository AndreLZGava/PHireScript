<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Statement;

use PHireScript\Compiler\Parser\Ast2\Statements;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Collection;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class RightParenthesisTyping extends Statements
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isSymbol() &&
        $token->value === ')';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $currentElement = $parseContext->context->getCurrentContextElement();
        if (
            $currentElement instanceof Collection
        ) {
            $parseContext->context->exitContext();
        }

        if ($currentElement instanceof CastingNode) {
            $casting = $parseContext->context->getCurrentContextElement();
            $parseContext->context->exitContext();
            $toCast = $parseContext->context->getCurrentContextElement();
            $toCast->right = $casting;
            $toCast->left->type = $casting;
        }
        return null;
    }
}
