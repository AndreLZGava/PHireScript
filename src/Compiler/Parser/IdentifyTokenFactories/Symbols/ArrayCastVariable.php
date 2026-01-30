<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactoryInterface;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class ArrayCastVariable extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $parseContext->tokenManager->getCurrentToken()->value === '=' &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->isType() &&
        $parseContext->tokenManager->getNextTokenAfterCurrent()->value === 'Array';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $previous = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $parseContext->tokenManager->getCurrentToken();
        $parseContext->tokenManager->walk(2);
        $object = current($this->getArgs('casting'));
        $varValue = new ArrayLiteralNode($parseContext->tokenManager->getCurrentToken(), [$object]);
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
