<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\RuntimeClass;

class BlockBrackets extends GlobalFactory
{
    public function isTheCase()
    {
        return in_array($this->tokenManager->getCurrentToken()->value, RuntimeClass::BLOCK_DELIMITERS, true);
    }

    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
