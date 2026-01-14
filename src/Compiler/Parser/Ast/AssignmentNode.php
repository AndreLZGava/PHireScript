<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

class AssignmentNode extends Statement
{
    public function __construct(
        public Node $left,
        public Node $right
    ) {
    }
}
