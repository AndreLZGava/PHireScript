<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\TraitDefinition;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class TraitKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $node = new TraitDefinition($this->tokenManager->getCurrentToken());
        $node->type = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();

        $node->name = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();
        $node->traits = $this->getWith($node);
        $node->body = $this->getContentBlock($node);
        return $node;
    }
}
