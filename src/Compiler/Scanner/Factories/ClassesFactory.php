<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Managers\TokenManager;
use PHPScript\Helper\Debug\Debug;

abstract class ClassesFactory extends GlobalFactory {
  public function getContentBlock($context): array {
    $codeBlock = $this->codeBlock();

    $factories = FactoryInitializer::getFactories();
    $result = [];

    $newTokenManager = new TokenManager($context, $codeBlock, 0);

    foreach ($codeBlock as $id => $token) {
      $returned = (new $factories[$token['type']]($newTokenManager))
        ->process();

      if ($returned) {
        $result[] = $returned;
      }
    }
    Debug::show($result, $codeBlock);
    exit;
    return ['test'];
  }

  public function codeBlock(): array {
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

    $tokensOfThisBlock = array_slice($tokensOfThisBlock, 0, $keyToken);

    return $tokensOfThisBlock;
  }
}
