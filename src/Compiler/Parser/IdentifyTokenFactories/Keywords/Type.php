<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;

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
