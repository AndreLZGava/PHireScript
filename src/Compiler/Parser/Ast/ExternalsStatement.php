<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

class ExternalsStatement extends Statement
{
    public function __construct(
        public readonly array $namespaces = [],
    ) {
    }
}
