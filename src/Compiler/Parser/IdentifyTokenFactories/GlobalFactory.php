<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Program;

abstract class GlobalFactory
{
    public function __construct(protected TokenManager $tokenManager)
    {
    }

    abstract public function process(Program $program): ?Node;
}
