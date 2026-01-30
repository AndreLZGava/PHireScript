<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class ExternalsStatement extends Statement
{
    public function __construct(
        Token $token,
        public readonly array $namespaces = [],
    ) {
    }
}
