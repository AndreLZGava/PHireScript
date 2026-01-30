<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class ExplicitTypedNode extends Expression
{
    public string $type;
    public function __construct(
        Token $token,
    ) {
        $this->type = $token->value;
    }
}
