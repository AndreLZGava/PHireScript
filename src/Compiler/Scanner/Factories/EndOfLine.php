<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Node;

class EndOfLine extends GlobalFactory {
  public function process(): ?Node {
    return null;
  }
}
