<?php

namespace PHPScript\Compiler\Parser\Ast;

class ReturnNode extends Statement
{
    public function __construct(
        public ?Expression $expression = null
    ) {
    }
}
