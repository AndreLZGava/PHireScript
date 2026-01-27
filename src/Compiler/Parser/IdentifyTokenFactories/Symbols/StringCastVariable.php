<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class StringCastVariable extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;

    public function isTheCase()
    {
        return $this->tokenManager->getCurrentToken()->value === '=' &&
        $this->tokenManager->getNextTokenAfterCurrent()->isType() &&
        $this->tokenManager->getNextTokenAfterCurrent()->value === 'String';
    }

    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $this->parseContext = $parseContext;
        $previous = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();

        $this->tokenManager->walk(2);
        $value = current($this->getArgs('casting'))->value;
        $varValue = new StringNode($this->tokenManager->getCurrentToken(), (string) "'" . $value . "'");

        $assignment = new VariableDeclarationNode(
            token: $currentToken,
            name: $previous->value,
            value: $varValue,
            type: null,
        );
        $parseContext->variables->addVariable($assignment);

        return $assignment;
    }
}
