<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;

class ExplicitTypedNode extends Expression
{
    public string $type;
    public function __construct(
        Token $token,
    ) {
        $this->type = $token->value;
    }
}
