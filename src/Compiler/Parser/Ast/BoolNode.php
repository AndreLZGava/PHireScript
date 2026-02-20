<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Ast\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class BoolNode extends Expression implements Type
{
    private string $raw = 'Bool';
    public function __construct(
        Token $token,
        public bool $value,
    ) {
    }

    public function getRawType(): string
    {
        return $this->raw;
    }
}
