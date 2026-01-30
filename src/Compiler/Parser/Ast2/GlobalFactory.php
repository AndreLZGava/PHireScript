<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

abstract class GlobalFactory
{
    abstract public function isTheCase(Token $token, ParseContext $parseContext): bool;

    abstract public function process(Token $token, ParseContext $parseContext): ?Node;
}
