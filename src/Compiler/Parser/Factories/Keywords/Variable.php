<?php

namespace PHPScript\Compiler\Parser\Factories\Keywords;

use PHPScript\Compiler\Parser\ClassDefinition;
use PHPScript\Compiler\Parser\Factories\ClassesFactory;
use PHPScript\Compiler\Parser\GlobalStatement;
use PHPScript\Compiler\Parser\Node;
use PHPScript\Helper\Debug\Debug;

class Variable extends ClassesFactory {
  public function process(): ?Node {

    $node = new GlobalStatement();
    $node->code = $this->tokenManager->getCurrentToken()['value'];
    return $node;
  }
}
