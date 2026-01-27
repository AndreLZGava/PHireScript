<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class EndOfLine extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        return null;
    }
}
