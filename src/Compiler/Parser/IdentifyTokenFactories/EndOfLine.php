<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;

class EndOfLine extends GlobalFactory {
  public function process(): ?Node {
    return null;
  }
}
