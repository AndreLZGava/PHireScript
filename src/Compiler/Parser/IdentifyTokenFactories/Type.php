<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

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
