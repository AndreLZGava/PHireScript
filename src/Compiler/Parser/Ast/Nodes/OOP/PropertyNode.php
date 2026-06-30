<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\OOP;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class PropertyNode extends Node
{
    public function __construct(
        public Token $token,
        public array $types,
        public string $name = '',
        public ?Node $value = null,
        public array $modifiers = [],
        public array $resolvedTypeInfo = [],
        public ?string $getter = null,
        public ?string $setter = null,
    ) {
    }
}
