<?php

namespace PHPScript\Compiler\Parser\Ast;

class PropertyAccessNode extends Expression
{
    public function __construct(
        public Node $object,
        public Node|string $property
    ) {
    }
}
