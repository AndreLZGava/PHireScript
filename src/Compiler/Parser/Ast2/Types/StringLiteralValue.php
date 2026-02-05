<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class StringLiteralValue extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isStringLiteral();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $stringNode = new StringNode($token, $token->value);
        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $stringNode;
            $current->left->type = $stringNode;
        }

        if($current instanceof CastingNode) {
            $current->value = $stringNode;
        }

        return  null;
    }
}
