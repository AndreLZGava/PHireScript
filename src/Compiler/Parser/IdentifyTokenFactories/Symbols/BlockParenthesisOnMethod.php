<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\RuntimeClass;

class BlockParenthesisOnMethod extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return in_array($token->value, RuntimeClass::START_END_ARGUMENTS, true) &&
            $parseContext->tokenManager->getContext() === RuntimeClass::CONTEXT_GET_ARGUMENTS;
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
