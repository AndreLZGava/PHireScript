<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Managers\TokenManager;

abstract class GlobalFactory
{
    public function __construct(protected TokenManager $tokenManager)
    {
    }

    abstract public function process(): ?Node;
}
