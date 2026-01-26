<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class VariableNode extends Statement
{
    public function __construct(
        public Token $token,
        public string $name,
    ) {
    }
}
