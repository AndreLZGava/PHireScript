<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class VariableDeclarationNode extends Statement
{
    public function __construct(
        public Token $token,
        public string $name,
        public ?string $type,
        public Expression $value,
        public bool $isConst = false
    ) {
    }
}
