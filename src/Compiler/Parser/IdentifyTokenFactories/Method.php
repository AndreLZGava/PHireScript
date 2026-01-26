<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class Method extends ClassesFactory
{
    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $this->parseContext = $parseContext;

        $previousToken = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $nextToken = $this->tokenManager->getNextTokenAfterCurrent();
        $context = $this->tokenManager->getContext();
        if (
            $context === 'casting' &&
            $currentToken->isIdentifier()
        ) {
            return new VariableNode($currentToken, $currentToken->value);
        }

        if (
            $currentToken->isIdentifier() &&
            $previousToken->value === '(' &&
            $nextToken->isIdentifier()
        ) {
            $type = new Type($this->tokenManager);
            return $type->process($this->program, $this->parseContext);
        }

        if (
            $nextToken->isSymbol() &&
            in_array($nextToken->value, ['?', '!', '('], true)
        ) {
            $this->tokenManager->walk(in_array($nextToken->value, ['?', '!'], true) ? 2 : 1);
            $node = new MethodDefinition($this->tokenManager->getCurrentToken());
            $node->name = trim((string) $currentToken->value);
            $node->mustBeBool = $nextToken->value === '?';
            $node->mustBeVoid = $nextToken->value === '!';
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
