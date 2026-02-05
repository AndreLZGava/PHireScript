<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class NumberLiteralValue extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isNumber();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $nodeValue = new NumberNode($token, filter_var($token->value, FILTER_VALIDATE_FLOAT));
        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $nodeValue;
            $current->left->type = $nodeValue;
        }

        if ($current instanceof CastingNode) {
            $current->value = $nodeValue;
        }

        return null;
    }
}
