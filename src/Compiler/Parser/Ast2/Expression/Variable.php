<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Expression;

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
        return $token->isIdentifier() && $parseContext->variables->getVariable($token->value);
    }
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $variable = $parseContext->variables->getVariable($token->value);
        $parseContext->definePrevious($variable);
        return $variable;
    }
}
