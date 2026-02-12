<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class CommaFactory extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $context): bool
    {
        return $token->value === ',';
    }

    public function process(Token $token, ParseContext $context): ?Node
    {
        return null;
    }
}
