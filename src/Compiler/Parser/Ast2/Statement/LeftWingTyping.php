<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Statement;

use PHireScript\Compiler\Parser\Ast2\Statements;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class LeftWingTyping extends Statements
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isSymbol() &&
        $token->value === '<' &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->isType();
    }
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
