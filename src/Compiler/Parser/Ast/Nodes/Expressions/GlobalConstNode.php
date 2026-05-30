<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression\Types\Type;

class GlobalConstNode extends Expression implements Type
{
    public string $value;
    public self $type;

    public function __construct(
        public Token $token,
    ) {
        $this->value = $token->value;
        $this->type  = $this;
    }

    public function getRawType(): string
    {
        return 'GlobalConst';
    }
}
