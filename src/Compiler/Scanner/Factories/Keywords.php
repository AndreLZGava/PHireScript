<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Factories\Keywords\Type;
use PHPScript\Compiler\Scanner\Node;

class Keywords extends GlobalFactory {

  private array $factories;

  public function process(): ?Node {
    $this->factories = [
      'type' => Type::class,
      'var' => Variable::class,
    ];

    $tokenValue = $this->tokenManager->getCurrentToken()['value'];
    $class = $this->factories[$tokenValue] ?? General::class;
    $processor = new $class($this->tokenManager);

    return $processor->process();
  }
}
