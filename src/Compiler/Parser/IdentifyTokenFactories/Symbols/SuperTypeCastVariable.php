<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\SuperTypeNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class SuperTypeCastVariable extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $parseContext->tokenManager->getCurrentToken()->value === '=' &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isType() &&
            in_array(
                $parseContext->tokenManager->getNextTokenAfterCurrent()->value,
                [
                    'Uuid',
                    'CardNumber',
                    'Color',
                    'Cron',
                    'Cvv',
                    'Duration',
                    'Email',
                    'ExpiryDate',
                    'Ipv4',
                    'Ipv6',
                    'Json',
                    'Mac',
                    'Slug',
                    'Url'
                ]
            );
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $previous = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $parseContext->tokenManager->getCurrentToken();
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
        $parseContext->tokenManager->walk(2);
        $argument = current($this->getArgs('casting')) ?: (object) ['value' => null];
        $value = $argument instanceof VariableNode ? $argument->name : $argument->value;
        $varValue = new SuperTypeNode($next, $value);
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
