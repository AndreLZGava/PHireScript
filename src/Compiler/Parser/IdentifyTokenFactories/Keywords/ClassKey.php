<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class ClassKey extends ClassesFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $node = new ClassDefinition($this->tokenManager->getCurrentToken());
        $previous = $this->tokenManager->getPreviousTokenBeforeCurrent();
        if (in_array($previous->value, ['abstract', 'readonly', '*', '#', '+'])) {
            $node->modifiers[] = $previous->value;
        }
        $node->type = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();
        $node->name = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();
        $node->traits = $this->getWith($node);
        $node->implements = $this->getImplements($node);
        $node->extends = $this->getExtends($node);
        $node->body = $this->getContentBlock($node);
        return $node;
    }
}
