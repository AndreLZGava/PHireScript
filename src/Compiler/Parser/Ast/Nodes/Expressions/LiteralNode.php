<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression\Types\Type;

class LiteralNode extends Expression implements Type
{
    public self $type;

    public function __construct(
        Token $token,
        public mixed $value,
        public string $rawType
    ) {
        $this->type = $this;
    }

    public function getRawType(): string
    {
        return $this->rawType;
    }
}
