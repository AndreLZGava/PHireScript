<?php

namespace PHPScript\Compiler\Parser\Factories\Keywords;

use PHPScript\Compiler\Parser\ClassDefinition;
use PHPScript\Compiler\Parser\Factories\ClassesFactory;
use PHPScript\Compiler\Parser\Node;
use PHPScript\Helper\Debug\Debug;

class Type extends ClassesFactory {
  public function process(): ?Node {

    $node = new ClassDefinition();
    $node->type = $this->tokenManager->getCurrentToken()['value'];
    $this->tokenManager->advance();
    // @todo implement validations to walk and validate its a name
    $node->name = $this->tokenManager->getCurrentToken()['value'];
    $this->tokenManager->advance();
    $node->body = $this->getContentBlock('type');
    return $node;
  }
}
