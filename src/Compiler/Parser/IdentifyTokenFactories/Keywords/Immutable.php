<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class Immutable extends ClassesFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->program = $program;

        $node = new ClassDefinition($this->tokenManager->getCurrentToken());
        $node->type = $this->tokenManager->getCurrentToken()->value;
        $node->readOnly = true;
        //$this->tokenManager->advance();
        $node->name = $this->tokenManager->getCurrentToken()->value;
        //$this->tokenManager->advance();
        $node->body = $this->getContentBlock($node);
        return $node;
    }
}
