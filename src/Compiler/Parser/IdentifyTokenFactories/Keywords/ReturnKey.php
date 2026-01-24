<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\ReturnNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataModelingTrait;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class ReturnKey extends ClassesFactory
{
    use DataModelingTrait;

    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $this->tokenManager->advance();
        $expression = $this->parseExpression();

        $returnNode = new ReturnNode($expression);
        $returnNode->line = $this->tokenManager->getCurrentToken()['line'];
        return $returnNode;
    }
}
