<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class StringNode extends Expression
{
    public function __construct(
        Token $token,
        public string $value,
    ) {
    }
}
