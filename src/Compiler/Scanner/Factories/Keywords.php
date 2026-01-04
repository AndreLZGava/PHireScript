<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Factories\Keywords\Type;
use PHPScript\Compiler\Scanner\Node;

class Keywords extends GlobalFactory {

  private array $factories;

  public function process(): ?Node {
    $this->factories = [
      'type' => new Type($this->tokenManager),
    ];
    return $this->factories[$this->tokenManager->getCurrentToken()['value']]->process();
  }
}
