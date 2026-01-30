<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class ConstructorDefinition extends Node
{
    public function __construct(
        Token $token,
        public array $modifiers = [],
        public array $params = [],
        public array $body = [],
    ) {
    }
}
