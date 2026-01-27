<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class StringLiteral extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        $currentToken = $this->tokenManager->getCurrentToken();
        $node = new StringNode($currentToken, $currentToken->value);
        return $node;
    }
}
