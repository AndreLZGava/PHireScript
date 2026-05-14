<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;

class NewExceptionNode extends Expression
{
    public function __construct(
        Token $token,
        public string $className,
        public string $message,
    ) {
    }
}
