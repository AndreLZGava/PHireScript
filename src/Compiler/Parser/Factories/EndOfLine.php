<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\Node;

class EndOfLine extends GlobalFactory {
  public function process(): ?Node {
    return null;
  }
}
