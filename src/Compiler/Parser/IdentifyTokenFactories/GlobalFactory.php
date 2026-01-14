<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Compiler\Program;

abstract class GlobalFactory
{
    public function __construct(protected TokenManager $tokenManager)
    {
    }

    abstract public function process(Program $program): ?Node;
}
