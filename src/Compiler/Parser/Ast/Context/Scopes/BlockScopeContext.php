<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Scopes;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * this is sketch, verify other files, maybe this is not necessary anymore, but if you implement it remove this doc line
 */
class BlockScopeContext extends ScopeContext
{
    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }
}
