<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;

class Comment extends GlobalFactory {
  public function process(): ?Node {
    $node = new GlobalStatement();
    $node->code = trim($this->tokenManager->getCurrentToken()['value']);
    return $node;
  }
}
