<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;

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
        return $token->isType() &&
        $token->value === 'Array';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $object = current($this->getArgs('casting'));
        $varValue = new ArrayLiteralNode($parseContext->tokenManager->getCurrentToken(), [$object]);

        return $varValue;
    }
}
