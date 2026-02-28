<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Ast\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Helper\Debug\Debug;

class NumberNode extends Expression implements Type
{
    public function __construct(
        public Token $token,
        public float|int $value,
    ) {
    }

    public function getRawType(): string
    {
        return is_int($this->value) ? 'Int' : 'Float';
    }
}
