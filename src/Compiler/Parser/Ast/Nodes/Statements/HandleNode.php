<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Statements;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsListNode;

class HandleNode extends Statement
{
    public function __construct(
        public Token $token,
        public ?ParamsListNode $param = null,
        public array $children = [],
    ) {
    }
}
