<?php

namespace PHPScript\Compiler\Parser\Ast\Expression;

class ArrayLiteral
{
    /** @var object[] */
    public array $items;
    public int $line;

    public function __construct(array $items, ?int $line = null)
    {
        $this->items = $items;
        $this->line = $line;
    }
}
