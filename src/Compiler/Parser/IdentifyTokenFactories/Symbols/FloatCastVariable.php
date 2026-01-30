<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataStringModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class FloatCastVariable extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;
    use DataStringModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $parseContext->tokenManager->getCurrentToken()->value === '=' &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isType() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->value === 'Float';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $previous = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $parseContext->tokenManager->getCurrentToken();

        $parseContext->tokenManager->walk(2);
        $value = current($this->getArgs('casting'))->value;
        $value = $this->clearQuotes($value);
        $varValue = new NumberNode($parseContext->tokenManager->getCurrentToken(), (float) $value);

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
