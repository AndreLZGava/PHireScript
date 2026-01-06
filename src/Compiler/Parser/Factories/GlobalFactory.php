<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Compiler\Parser\Node;

abstract class GlobalFactory {
  public function __construct(protected TokenManager $tokenManager) {
  }

  abstract public function process(): ?Node;
}
