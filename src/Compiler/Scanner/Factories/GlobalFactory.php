<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Managers\TokenManager;
use PHPScript\Compiler\Scanner\Node;

abstract class GlobalFactory {
  public function __construct(protected TokenManager $tokenManager) {
  }

  abstract public function process(): ?Node;
}
