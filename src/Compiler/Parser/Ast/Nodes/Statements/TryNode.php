<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\TryScopeNode;

class TryNode extends Statement
{
    public function __construct(
        public Token $token,
        public ?TryScopeNode $try = null,
        public array $handles = [],
        public ?AlwaysNode $always = null
    ) {
    }
}
