<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataStringModelingTrait;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;

class IntCastVariable extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;
    use DataStringModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() &&
            $token->value === 'Int';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $nodeValue = new NumberNode($token, 0);
        $casting = new CastingNode($token, 'int');
        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $nodeValue;
            $current->left->type = $nodeValue;
        }

        $parseContext->context->enterContext(Context::Casting, $casting);

        return null;
    }
}
