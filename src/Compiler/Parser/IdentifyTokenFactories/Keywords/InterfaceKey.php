<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\InterfaceDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class InterfaceKey extends ClassesFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->program = $program;

        $node = new InterfaceDefinition($this->tokenManager->getCurrentToken());
        $node->type = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();

        $node->name = $this->tokenManager->getCurrentToken()->value;
        $this->tokenManager->advance();
        $node->extends = $this->getExtendsInterface($node);
        $node->body = $this->getContentBlock($node);
        return $node;
    }
}
