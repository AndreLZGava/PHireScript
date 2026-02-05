<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StackNode;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class StackValue extends Collections
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() && $token->value === 'Stack';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $stack = new StackNode($token);

        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $stack;
            $current->left->type = $stack;
        }

        $parseContext->context->enterContext(Context::Stack, $stack);
        return null;
    }
}
