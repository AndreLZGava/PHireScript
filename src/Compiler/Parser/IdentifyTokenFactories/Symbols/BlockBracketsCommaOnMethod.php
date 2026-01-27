<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class BlockBracketsCommaOnMethod extends GlobalFactory
{
    public function isTheCase()
    {
        return in_array($this->tokenManager->getCurrentToken()->value, ['[', ']', ','], true) &&
        $this->tokenManager->getContext() === 'method';
    }

    public function process(Program $program): ?Node
    {
        $node = new GlobalStatement($this->tokenManager->getCurrentToken());
        $node->code = $this->tokenManager->getCurrentToken()->value;
        return $node;
    }
}
