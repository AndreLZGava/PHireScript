<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Literal;

/**
 * Apparently not used.
 */
class StringLiteral
{
    public function __construct(
        public string $value,
        public ?int $line = null
    ) {
    }
}
