<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Expression;

class ArrayLiteral
{
    public function __construct(
        /** @var object[] */
        public array $items,
        public int $line = null
    ) {
    }
}
