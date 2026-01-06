<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;

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
