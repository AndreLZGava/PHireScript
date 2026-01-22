<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class ConstructorDefinition extends Node
{
    public function __construct(
        public array $modifiers = [],
        public array $params = [],
        public array $body = [],
    ) {
    }
}
