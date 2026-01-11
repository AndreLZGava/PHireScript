<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Helper\Debug\Debug;

class GenericString extends ClassesFactory
{
    public function process(): ?Node
    {

        $previousToken = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

        if (
            $nextToken['type'] === 'T_SYMBOL' &&
            in_array($nextToken['value'], ['?', '!', '('])
        ) {
          //Debug::show($this->tokenManager->getAll());
            $this->tokenManager->walk(in_array($nextToken['value'], ['?', '!']) ? 2 : 1);
            $node = new MethodDefinition();
            $node->name = trim($currentToken['value']);
            $node->line = $currentToken['line'];
            $node->mustBeBool = $nextToken['value'] === '?';
            $node->mustBeVoid = $nextToken['value'] === '!';
            $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($previousToken);
            $node->args = $this->getArgs('arguments');
            $node->returnType = $this->getReturnType($node);
            $node->bodyCode = [];
            if ($this->tokenManager->getContext() === 'class') {
                $node->bodyCode = $this->getMethodBody($node);
            }

            return $node;
        }

        return null;
    }
}
