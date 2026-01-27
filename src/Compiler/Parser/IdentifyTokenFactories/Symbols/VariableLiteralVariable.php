<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use Exception;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class VariableLiteralVariable extends GlobalFactory
{
    public function isTheCase()
    {
        return $this->tokenManager->getCurrentToken()->value === '=' &&
        $this->tokenManager->getNextTokenAfterCurrent()->isIdentifier() &&
        $this->tokenManager->getNextToken()->isEndOfLine();
    }

    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        $previous = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $next = $this->tokenManager->getNextTokenAfterCurrent();
        $this->tokenManager->walk(2);
        if (empty($parseContext->variables->getVariable($next->value))) {
            throw new Exception("Variable {$next->value} is not defined yet!");
        }

        $reference = new VariableReferenceNode(
            token: $currentToken,
            name: $previous->value,
            value: $next->value,
            type: null,
        );

        $assignment = new VariableDeclarationNode(
            token: $currentToken,
            name: $previous->value,
            value: $reference,
            type: null,
        );

        $parseContext->variables->addVariable($assignment);

        return $assignment;
    }
}
