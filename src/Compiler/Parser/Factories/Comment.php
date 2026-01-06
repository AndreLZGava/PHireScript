<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\GlobalStatement;
use PHPScript\Compiler\Parser\Node;

class Comment extends GlobalFactory {
  public function process(): ?Node {
    $node = new GlobalStatement();
    $node->code = trim($this->tokenManager->getCurrentToken()['value']);
    return $node;
  }
}
