<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Literal;

/**
 * Apparently not used.
 */
class NullLiteral
{
    public function __construct(
        public null $value,
        public ?int $line = null
    ) {
    }
}
