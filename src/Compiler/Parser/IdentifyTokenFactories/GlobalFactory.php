<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

abstract class GlobalFactory
{
    public Program $program;
    public ParseContext $parseContext;

    public function __construct(protected TokenManager $tokenManager)
    {
    }

    // abstract public function isTheCase(ParseContext $parseContext): bool;

    abstract public function process(Program $program, ParseContext $parseContext): ?Node;
}
