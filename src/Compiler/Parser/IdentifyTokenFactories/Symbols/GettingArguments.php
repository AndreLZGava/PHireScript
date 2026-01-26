<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\RuntimeClass;

class GettingArguments extends GlobalFactory
{
    public function isTheCase()
    {
        return  in_array($this->tokenManager->getCurrentToken()->value, RuntimeClass::START_END_ARGUMENTS, true)
            && $this->tokenManager->getContext() === RuntimeClass::CONTEXT_GET_ARGUMENTS;
    }

    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
