<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class KeyValuePairNode extends Node
{
    public function __construct(
        public ?Node $key,
        public ?Node $value
    ) {
    }
}
