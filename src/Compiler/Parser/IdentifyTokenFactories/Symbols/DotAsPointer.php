<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class DotAsPointer extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $parseContext->tokenManager->getPreviousTokenBeforeCurrent()->isIdentifier() &&
            $parseContext->tokenManager->getCurrentToken()->value === '.' &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isIdentifier();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        // Debug::show($parseContext->tokenManager->getLeftTokens());exit;
        Debug::show('DotAsPointer');
        return null;
    }
}
