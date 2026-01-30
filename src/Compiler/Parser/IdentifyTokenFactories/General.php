<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class General extends GlobalFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $node = new GlobalStatement($this->tokenManager->getCurrentToken());

        $node->code = trim((string) $this->tokenManager->getCurrentToken()->value);
        return $node;
    }
}
