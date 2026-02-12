<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Statement;

use PHireScript\Compiler\Parser\Ast2\Statements;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Expression;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class EndOfLine extends Statements
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $currentElement = $parseContext->context->current()->element;

        if (
            $currentElement instanceof AssignmentNode
        ) {
            $parseContext->context->exitContext();
            return $currentElement;
        }

        return null;
    }
}
