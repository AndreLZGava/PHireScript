<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use Exception;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class VariableLiteralReference extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isIdentifier() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isEndOfLine();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $parseContext->tokenManager->advance();
        if (empty($parseContext->variables->getVariable($token->value))) {
            throw new Exception("Variable {$token->value} is not defined yet!");
        }

        return new VariableReferenceNode(
            token: $token,
            name: $token->value,
            value: $token->value,
            type: null,
        );
    }
}
