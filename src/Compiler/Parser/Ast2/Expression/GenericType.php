<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Expression;

use PHireScript\Compiler\Parser\Ast2\Expressions;
use PHireScript\Compiler\Parser\Ast\ExplicitTypedNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class GenericType extends Expressions
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $typed = new ExplicitTypedNode($token);
        $element = $parseContext->context->getCurrentContextElement();
        if (isset($element->types)) {
            $element->types[] = $typed;
        }
        return null;
    }
}
