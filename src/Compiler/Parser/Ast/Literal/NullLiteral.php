<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast\Literal;

class NullLiteral
{
    public function __construct(
        public null $value,
        public ?int $line = null
    ) {
    }
}
