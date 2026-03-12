<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Ast\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class SuperTypeNode extends Expression implements Type
{
    public string $type;
    public function __construct(
        public Token $token,
        public mixed $value = null,
    ) {
        $this->type = $token->value;
    }

    public function getRawType(): string
    {
        return $this->type;
    }
}
