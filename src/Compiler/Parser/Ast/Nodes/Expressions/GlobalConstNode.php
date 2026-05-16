<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;

class GlobalConstNode extends Expression
{
    public string $value;
    public function __construct(
        public Token $token,
    ) {
        $this->value = $token->value;
    }
}
