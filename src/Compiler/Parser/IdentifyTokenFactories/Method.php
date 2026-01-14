<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;
use PHPScript\Runtime\RuntimeClass;

class Method extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $previousToken = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

        if (
            $nextToken['type'] === 'T_SYMBOL' &&
            in_array($nextToken['value'], ['?', '!', '('], true)
        ) {
            $this->tokenManager->walk(in_array($nextToken['value'], ['?', '!'], true) ? 2 : 1);
            $node = new MethodDefinition();
            $node->name = trim((string) $currentToken['value']);
            $node->line = $currentToken['line'];
            $node->mustBeBool = $nextToken['value'] === '?';
            $node->mustBeVoid = $nextToken['value'] === '!';
            $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($previousToken);
            $node->args = $this->getArgs(RuntimeClass::CONTEXT_GET_ARGUMENTS);
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
