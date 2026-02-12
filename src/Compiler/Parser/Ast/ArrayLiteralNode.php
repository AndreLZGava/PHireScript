<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class ArrayLiteralNode extends Expression
{
    public function __construct(
        Token $token,
        public array $elements
    ) {
    }

    public function addChild(Node $node): void
    {
        $this->elements[] = $node;
    }
}
