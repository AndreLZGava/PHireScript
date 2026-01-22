<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class IssetOperatorNode extends Statement
{
    public function __construct(
        public mixed $target
    ) {
    }
}
