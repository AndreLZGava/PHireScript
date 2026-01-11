<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Helper\Debug\Debug;

class Number extends GlobalFactory
{
    public function process(): ?Node
    {
        Debug::show($this->tokenManager->getCurrentToken());
        exit;
        $node = new GlobalStatement();
        $node->code = trim($this->tokenManager->getCurrentToken()['value']);
        return $node;
    }
}
