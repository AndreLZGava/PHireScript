<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Literal;

/**
 * Apparently not used.
 */
class IntLiteral
{
    public function __construct(
        public int $value,
        public ?int $line = null
    ) {
    }
}
