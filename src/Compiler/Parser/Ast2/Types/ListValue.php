<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\ListNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class ListValue extends Collections
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() && $token->value === 'List';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $list = new ListNode($token);

        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $list;
            $current->left->type = $list;
        }

        $parseContext->context->enterContext(Context::List, $list);
        return null;
    }
}
