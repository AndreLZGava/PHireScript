<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;

class Variable extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $node = new GlobalStatement();
        $node->code = $this->tokenManager->getCurrentToken()['value'];
        return $node;
    }
}
