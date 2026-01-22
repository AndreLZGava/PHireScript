<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class Variable extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $node = new VariableDeclarationNode();
        $node->code = $this->tokenManager->getCurrentToken()['value'];
        return $node;
    }
}
