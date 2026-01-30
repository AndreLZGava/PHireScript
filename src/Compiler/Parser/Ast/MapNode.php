<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class MapNode extends Collection
{
    public function __construct(
        Token $token,
        public array $types = [],
    ) {
    }
}
