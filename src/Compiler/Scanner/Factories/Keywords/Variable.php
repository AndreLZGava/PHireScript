<?php

namespace PHPScript\Compiler\Scanner\Factories\Keywords;

use PHPScript\Compiler\Scanner\ClassDefinition;
use PHPScript\Compiler\Scanner\Factories\ClassesFactory;
use PHPScript\Compiler\Scanner\GlobalStatement;
use PHPScript\Compiler\Scanner\Node;
use PHPScript\Helper\Debug\Debug;

class Variable extends ClassesFactory {
  public function process(): ?Node {

    $node = new GlobalStatement();
    $node->code = $this->tokenManager->getCurrentToken()['value'];
    return $node;
  }
}
