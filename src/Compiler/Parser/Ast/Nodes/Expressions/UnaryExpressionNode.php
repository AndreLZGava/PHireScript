<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Ast\Nodes\Expression;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class UnaryExpressionNode extends Expression
{
    public function __construct(
        Token $token,
        public string $operator,
        public ?Node $operand = null
    ) {
    }
}
