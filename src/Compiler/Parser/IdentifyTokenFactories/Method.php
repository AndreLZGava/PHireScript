<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class Method extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $previousToken = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $nextToken = $this->tokenManager->getNextTokenAfterCurrent();
        $context = $this->tokenManager->getContext();
        if (
            $context === 'casting' &&
            $currentToken['type'] === 'T_IDENTIFIER'
        ) {
            $variable = new VariableNode($currentToken['value']);
            $variable->line = $currentToken['line'];
            return $variable;
        }

        if (
            $currentToken['type'] === 'T_IDENTIFIER' &&
            $previousToken['value'] === '(' &&
            $nextToken['type'] === 'T_IDENTIFIER'
        ) {
            $type = new Type($this->tokenManager);
            return $type->process($this->program);
        }

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
