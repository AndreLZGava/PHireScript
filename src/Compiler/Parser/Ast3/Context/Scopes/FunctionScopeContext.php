<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Scopes;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;

class FunctionScopeContext extends ScopeContext
{
    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
