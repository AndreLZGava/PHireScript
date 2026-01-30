<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class BlockBracketsCommaOnMethod extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return in_array($parseContext->tokenManager->getCurrentToken()->value, ['[', ']', ','], true) &&
            $parseContext->tokenManager->getContext() === 'method';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $node = new GlobalStatement($parseContext->tokenManager->getCurrentToken());
        $node->code = $parseContext->tokenManager->getCurrentToken()->value;
        return $node;
    }
}
