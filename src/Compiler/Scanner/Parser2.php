<?php

namespace PHPScript\Compiler\Scanner;

use PHPScript\Compiler\Scanner\Program;
use PHPScript\Compiler\Scanner\Factories\FactoryInitializer;
use PHPScript\Compiler\Scanner\Managers\TokenManager;

class Parser2 {
  private array $factories;

  public function __construct() {
    $this->factories = FactoryInitializer::getFactories();
  }

  public function parse($tokens): Program {
    $program = new Program();
    $tokenManager = new TokenManager('general', $tokens, 0);

    while (!$tokenManager->isEndOfTokens()) {
      $token = $tokenManager->getCurrentToken();

      $result =  (new $this->factories[$token['type']]($tokenManager))
        ->process();

      if ($result) {
        $program->statements[] = $result;
      }

      $tokenManager->advance();
    }

    return $program;
  }
}
