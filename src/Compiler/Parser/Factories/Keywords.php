<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\Factories\Keywords\Type;
use PHPScript\Compiler\Parser\Node;

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
