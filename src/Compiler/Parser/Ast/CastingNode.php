<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class CastingNode extends Expression
{
    public function __construct(
        Token $token,
        public string $to,
        public mixed $value = null,
    ) {
    }
}
