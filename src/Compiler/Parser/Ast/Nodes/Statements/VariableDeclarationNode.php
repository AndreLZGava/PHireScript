<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;

class VariableDeclarationNode extends Statement
{
    public function __construct(
        Token $token,
        public string $name,
        public ?Node $value = null,
        public ?Node $type = null,
        public bool $isConst = false
    ) {
    }
}
