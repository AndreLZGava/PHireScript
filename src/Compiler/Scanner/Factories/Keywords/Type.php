<?php

namespace PHPScript\Compiler\Scanner\Factories\Keywords;

use PHPScript\Compiler\Scanner\ClassDefinition;
use PHPScript\Compiler\Scanner\Factories\ClassesFactory;
use PHPScript\Compiler\Scanner\Node;
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
