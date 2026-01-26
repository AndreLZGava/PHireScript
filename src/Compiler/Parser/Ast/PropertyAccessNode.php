<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class PropertyAccessNode extends Expression
{
    public function __construct(
        public Token $token,
        public Node $object,
        public Node|string $property
    ) {
    }
}
