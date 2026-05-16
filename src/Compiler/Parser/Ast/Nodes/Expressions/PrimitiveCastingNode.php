<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;

class PrimitiveCastingNode extends Expression
{
    public function __construct(
        public Token $token,
        public string $to,
        public mixed $value = null,
    ) {
    }
}
