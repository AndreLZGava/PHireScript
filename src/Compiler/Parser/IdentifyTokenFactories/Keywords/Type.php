<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHPScript\Compiler\Program;

class Type extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $node = new ClassDefinition();
        $node->type = $this->tokenManager->getCurrentToken()['value'];
        $node->line = $this->tokenManager->getCurrentToken()['line'];
        $this->tokenManager->advance();
        // @todo implement validations to walk and validate its a name
        $node->name = $this->tokenManager->getCurrentToken()['value'];
        $this->tokenManager->advance();
        $node->body = $this->getContentBlock('type');
        return $node;
    }
}
