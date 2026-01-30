<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers\Context;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;

class ContextState
{
    public function __construct(
        public Context $context,
        public Node $element
    ) {
    }
}
