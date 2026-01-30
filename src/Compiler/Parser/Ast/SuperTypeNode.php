<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class SuperTypeNode extends Expression
{
    public string $type;
    public function __construct(
        Token $token,
        public mixed $value,
    ) {
        $this->type = $token->value;
    }
}
