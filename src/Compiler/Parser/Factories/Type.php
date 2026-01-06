<?php

namespace PHPScript\Compiler\Parser\Factories;

use PHPScript\Compiler\Parser\GlobalStatement;
use PHPScript\Compiler\Parser\Node;
use PHPScript\Compiler\Parser\PropertyDefinition;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Helper\Debug\Debug;
use TypeError;

class Type extends GlobalFactory {
  public function process(): ?Node {

    $node = new PropertyDefinition();

    $node->modifiers[] = ModifiersTransform::map($this->tokenManager->getPreviousTokenBeforeCurrent());

    while (!$this->tokenManager->isEndOfTokens()) {

      $currentToken = $this->tokenManager->getCurrentToken();
      $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

      $this->tokenManager->advance();
      $node->type = $currentToken['value'];
      if ($nextToken['type'] === 'T_IDENTIFIER') {
        $node->name = $nextToken['value'];
        break;
      }
    }

    return $node;
  }
}
