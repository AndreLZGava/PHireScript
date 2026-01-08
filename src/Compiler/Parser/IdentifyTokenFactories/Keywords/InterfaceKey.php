<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHPScript\Helper\Debug\Debug;

class InterfaceKey extends ClassesFactory
{
    public function process(): ?Node
    {
        $node = new ClassDefinition();
        $node->type = $this->tokenManager->getCurrentToken()['value'];
        $this->tokenManager->advance();

        $node->name = $this->tokenManager->getCurrentToken()['value'];
        $this->tokenManager->advance();
        $node->body = $this->getContentBlock($node->type);
        return $node;
    }
}
