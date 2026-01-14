<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;

class General extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        $node = new GlobalStatement();

        $node->code = trim($this->tokenManager->getCurrentToken()['value']);
        return $node;
    }
}
