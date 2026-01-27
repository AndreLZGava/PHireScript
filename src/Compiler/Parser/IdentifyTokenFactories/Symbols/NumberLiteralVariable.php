<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class NumberLiteralVariable extends GlobalFactory
{
    public function isTheCase()
    {
        return $this->tokenManager->getCurrentToken()->value === '=' &&
            $this->tokenManager->getNextTokenAfterCurrent()->isNumber();
    }

    public function process(Program $program): ?Node
    {
        $previous = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $next = $this->tokenManager->getNextTokenAfterCurrent();
        $this->tokenManager->walk(2);
        $varValue = new NumberNode($next, (float) $next->value);

        $assignment = new VariableDeclarationNode(
            token: $currentToken,
            name: $previous->value,
            value: $varValue,
            type: null,
        );
        $this->parseContext->variables->addVariable($assignment);

        return $assignment;
    }
}
