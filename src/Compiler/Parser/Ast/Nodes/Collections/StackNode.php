<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Collections;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Collection;

class StackNode extends Collection
{
    public function __construct(
        public Token $token,
        public array $types = [],
    ) {
    }
}
