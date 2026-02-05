<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\SuperTypeNode;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class SuperTypeCastVariable extends GlobalFactory
{
   // use DataArrayObjectModelingTrait;
    //use DataParamsModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() &&
            in_array(
                $token->value,
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
        //$parseContext->tokenManager->walk(2);
        $argument = current($this->getArgs('casting')) ?: (object) ['value' => null];
        $value = $argument instanceof VariableNode ? $argument->name : $argument->value;
        $varValue = new SuperTypeNode($token, $value);
        return $varValue;
    }
}
