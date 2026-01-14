<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Program;

class EndOfLine extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        return null;
    }
}
