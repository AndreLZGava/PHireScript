<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Statement;

use PHireScript\Compiler\Parser\Ast2\Statements;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class Variable extends Statements
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isIdentifier() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isSymbol() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '=';
    }
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $variable = new VariableDeclarationNode(
            token: $token,
            name: $token->value,
        );

        $parseContext->context->definingVariable($variable);
        $parseContext->variables->addVariable($variable);
        //$parseContext->tokenManager->advance();
        return null;
    }
}
