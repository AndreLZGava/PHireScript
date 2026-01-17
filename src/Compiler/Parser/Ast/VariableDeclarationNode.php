<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class VariableDeclarationNode extends Statement
{
    public function __construct(
        public string $name,
        public ?string $type,
        public Expression $value,
        public bool $isConst = false
    ) {
    }
}
