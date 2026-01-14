<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;
use PHPScript\Runtime\RuntimeClass;

class Type extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        $node = new PropertyDefinition();
        $allowNull = false;
        if (
            $this->tokenManager->getContext() !== RuntimeClass::CONTEXT_GET_ARGUMENTS
        ) {
            $token = $this->tokenManager->getPreviousTokenBeforeCurrent();
            if ($token['value'] === '?') {
                $token = $this->tokenManager->getPreviousToken();
                $allowNull = true;
            }

            $node->modifiers[] = (
                new ModifiersTransform($this->tokenManager))
                ->map(
                    $token
                );
        }

        while (!$this->tokenManager->isEndOfTokens()) {
            $currentToken = $this->tokenManager->getCurrentToken();
            $nextToken = $this->tokenManager->getNextTokenAfterCurrent();
            $this->tokenManager->advance();
            $node->type = $currentToken['value'];
            if ($nextToken['type'] === 'T_IDENTIFIER') {
                $node->name = $nextToken['value'];
                $nextAfterVariableName = $this->tokenManager->getNextToken();
                if ($nextAfterVariableName['value'] == '=') {
                    $nextAfterEqual = $this->tokenManager->getNextToken();
                    $node->defaultValue = $nextAfterEqual['value'];
                    $this->tokenManager->walk(2);
                }
                break;
            }
        }

        if ($allowNull) {
            $node->type = "Null|" . $node->type;
        }
        return $node;
    }
}
