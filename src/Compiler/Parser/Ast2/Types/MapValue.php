<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\MapNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class MapValue extends Collections
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() && $token->value === 'Map';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $map = new MapNode($token);

        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $map;
            $current->left->type = $map;
        }

        $parseContext->context->enterContext(Context::Map, $map);
        return null;
    }
}
