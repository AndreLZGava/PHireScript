<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Helper\Debug\Debug;

abstract class ClassesFactory extends GlobalFactory {
  public function getContentBlock($context): array {
    $codeBlockToken = $this->codeBlockToken();

    $factories = FactoryInitializer::getFactories();
    $result = [];

    $newTokenManager = new TokenManager($context, $codeBlockToken, 0);

    while (!$newTokenManager->isEndOfTokens()) {
      $token = $newTokenManager->getCurrentToken();

      $returned = (new $factories[$token['type']]($newTokenManager))
        ->process();

      if ($returned) {
        $result[] = $returned;
      }

      $newTokenManager->advance();
    }

    $this->tokenManager->walk(count($codeBlockToken));

    return $result;
  }

  public function codeBlockToken(): array {
    $openBrackets = [];
    $closeBrackets = [];
    $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());

    foreach ($tokensOfThisBlock as $keyToken => $token) {
      if ($token['value'] === '{') {
        $openBrackets[] = $token;
      }

      if ($token['value'] === '}') {
        $closeBrackets[] = $token;
        if (count($openBrackets) === count($closeBrackets)) {
          break;
        }
      }
    }

    $tokensOfThisBlock = array_slice($tokensOfThisBlock, 0, $keyToken + 1);

    return $tokensOfThisBlock;
  }
}
