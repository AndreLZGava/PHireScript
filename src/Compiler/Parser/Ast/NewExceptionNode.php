<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class NewExceptionNode extends Expression
{
    public function __construct(
        public string $className,
        public string $message,
    ) {
    }
}
