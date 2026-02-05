<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;

use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class StringCastValue extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() &&
            $token->value === 'String';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $varValue = new StringNode($token, (string) $token->value);
        $casting = new CastingNode($token, 'string');
        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $varValue;
            $current->left->type = $varValue;
        }

        $parseContext->context->enterContext(Context::Casting, $casting);

        return null;
    }
}
