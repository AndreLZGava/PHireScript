<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\BoolNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class BoolCastVariable extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() &&
            $token->value === 'Bool';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $varValue = new BoolNode($token, (bool) $token->value);
        $casting = new CastingNode($token, 'bool');
        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $varValue;
            $current->left->type = $varValue;
        }

        $parseContext->context->enterContext(Context::Casting, $casting);

        return null;
    }
}
