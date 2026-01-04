<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\GlobalStatement;
use PHPScript\Compiler\Scanner\Node;

class Variable extends GlobalFactory {
  public function process(): ?Node {

    $node = new GlobalStatement();
    $node->code = trim($this->tokenManager->getCurrentToken()['value']);
    return $node;
  }
}
