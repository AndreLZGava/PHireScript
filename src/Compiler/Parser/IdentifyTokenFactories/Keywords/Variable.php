<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;

class Variable extends ClassesFactory {
  public function process(): ?Node {

    $node = new GlobalStatement();
    $node->code = $this->tokenManager->getCurrentToken()['value'];
    return $node;
  }
}
