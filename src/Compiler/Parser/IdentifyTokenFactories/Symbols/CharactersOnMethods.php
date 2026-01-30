<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\RuntimeClass;

class CharactersOnMethods extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return in_array($parseContext->tokenManager->getCurrentToken()->value, RuntimeClass::CHARACTERS_ON_METHODS, true) &&
            in_array($parseContext->tokenManager->getContext(), RuntimeClass::OBJECT_AS_CLASS, true);
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
