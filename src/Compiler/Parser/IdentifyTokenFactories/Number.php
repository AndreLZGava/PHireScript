<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class Number extends GlobalFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $currentToken = $this->tokenManager->getCurrentToken();
        $node = new NumberNode($currentToken, (float)$currentToken->value);
        return $node;
    }
}
