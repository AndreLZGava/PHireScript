<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Helper\Debug\Debug;

class Symbol extends GlobalFactory
{
    public function process(): ?Node
    {
        $currentToken = $this->tokenManager->getCurrentToken();
        $currentContext  = $this->tokenManager->getContext();

        if (
            in_array($currentToken['value'], ['(', ')'])
            && $currentContext === 'arguments'
        ) {
            return null;
        }

        if (
            in_array($currentToken['value'], ['[', ']', ','])
            && $currentContext === 'method'
        ) {
            $node = new GlobalStatement();
            $node->code = $currentToken['value'];
            return $node;
        }

        if (in_array($currentToken['value'], ['{', '}'])) {
            return null;
        }

        if (
            in_array($currentToken['value'], ['!', '?', ':']) &&
            in_array($currentContext, ['type', 'interface'])
        ) {
            return null;
        }

        if (
            in_array($currentToken['value'], ['+', '#']) &&
            $currentContext === 'type'
        ) {
            $node = new PropertyDefinition();

            $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($currentToken);

            return $this->parsePropertyWithTypes($node);
        }
        Debug::show(['currentToken' => $currentToken, 'context' => $currentContext]);
        return null;
    }

    private function parsePropertyWithTypes(PropertyDefinition $node): PropertyDefinition
    {
        $types = [];

        while (!$this->tokenManager->isEndOfTokens()) {
            $token = $this->tokenManager->getCurrentToken();

            if ($token['type'] === 'T_TYPE' || $this->isTypeFormat($token)) {
                $types[] = $token['value'];
            }

            $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

            $this->tokenManager->advance();

            if ($nextToken['type'] === 'T_IDENTIFIER') {
                $node->name = trim($nextToken['value']);
                break;
            }
        }

        $node->type = implode('|', $types);
        return $node;
    }

    private function isTypeFormat(array $token): bool
    {
        if ($token['type'] !== 'T_IDENTIFIER') {
            return false;
        }
        $value = $token['value'];
        $firstLetter = mb_substr($value, 0, 1);
        return $firstLetter === mb_strtoupper($firstLetter);
    }
}
