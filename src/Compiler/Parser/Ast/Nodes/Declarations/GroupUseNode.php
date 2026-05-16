<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;

class GroupUseNode extends Statement
{
    public function __construct(
        public Token $token,
        public array $parts = [],
    ) {
    }
}
