<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Program;

class Comment extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        $node = new GlobalStatement();
        $node->code = trim((string) $this->tokenManager->getCurrentToken()['value']);
        return $node;
    }
}
