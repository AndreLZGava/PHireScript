<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;

class MetaTypeNode extends Expression
{
    public string $type;
    public function __construct(
        Token $token,
        public mixed $value,
    ) {
        $this->type = $token->value;
    }
}
