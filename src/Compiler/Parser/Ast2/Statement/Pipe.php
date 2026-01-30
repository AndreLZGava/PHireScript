<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Statement;

use PHireScript\Compiler\Parser\Ast2\Statements as Ast2Statements;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class Pipe extends Ast2Statements
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isSymbol() && $token->value === '|';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
