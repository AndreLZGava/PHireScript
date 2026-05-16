<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;

class VariableNode extends Statement
{
    public function __construct(
        Token $token,
        public string $name,
    ) {
    }
}
