<?php

namespace PHPScript\Compiler\Scanner\Factories;

use PHPScript\Compiler\Scanner\Node;
use PHPScript\Compiler\Scanner\PropertyDefinition;
use PHPScript\Compiler\Scanner\Transformers\ModifiersTransform;
use PHPScript\Helper\Debug\Debug;

class Symbol extends GlobalFactory {
  public function process(): ?Node {

    if ($this->tokenManager->getCurrentToken()['value'] === '{' || $this->tokenManager->getCurrentToken()['value'] === '}') {
      return null;
    }

    if (
      in_array($this->tokenManager->getCurrentToken()['value'], ['+', '#']) &&
      in_array($this->tokenManager->getContext(), ['type'])
    ) {
      $node = new PropertyDefinition();
      $node = $this->handleMultipleTypes($node, $this->tokenManager->getCurrentToken());
      Debug::show('cheguei', $node);
      return $node;
    }

    Debug::show('cheguei aqui', $this->tokenManager->getCurrentToken());
    exit;
    return $node;
  }

  private function handleMultipleTypes($node, $currenToken) {
    Debug::show($currenToken);
    $typedToken = $this->tokenManager->getNextToken();
    $firstLetter = mb_substr($typedToken['value'], 0, 1);
    $isUpperCase = $firstLetter === mb_strtoupper($firstLetter);
    if ($typedToken['type'] === 'T_TYPE' || $isUpperCase) {
      $variableName = $this->tokenManager->getNextToken();
      $node->type = $typedToken['value'];

      if ($variableName['value'] === '|') {
        $node->type .= $variableName['value'];
        return $this->handleMultipleTypes($node, $typedToken);
      }

      $node->name = trim($variableName['value']);
      $node->modifiers[] = ModifiersTransform::map($currenToken['value']);
      return $node;
    }
  }
}
