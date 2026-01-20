<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\TraitDefinition;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class TraitKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $node = new TraitDefinition();
        $node->type = $this->tokenManager->getCurrentToken()['value'];
        $node->line = $this->tokenManager->getCurrentToken()['line'];
        $this->tokenManager->advance();

        $node->name = $this->tokenManager->getCurrentToken()['value'];
        $this->tokenManager->advance();
        $node->body = $this->getContentBlock($node->type);
        return $node;
    }
}
