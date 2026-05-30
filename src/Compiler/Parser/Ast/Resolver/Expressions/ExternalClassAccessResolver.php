<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class ExternalClassAccessResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        if (!$token->isIdentifier() && !$token->isGlobalConst()) {
            return false;
        }
        // Identifier or CONST followed by . (member access) or () (instantiation)
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
        return $parseContext->isExternalAlias($token->value) &&
            ($next->isDot() || $next->isOpeningParenthesis());
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();

        if ($next->isOpeningParenthesis()) {
            // External instantiation: ClassName() → new ClassName(args)
            $this->resolveInstantiation($token, $parseContext, $context);
            return;
        }

        // Member access: ClassName.member → placeholder for dot-chained access
        $node = new LiteralNode($token, $token->value, 'String');
        $parseContext->variables->setVirtualVariable($node);
        $context->addChild($node);
    }

    private function resolveInstantiation(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $method = new BaseMethods(
            name:                 '__construct',
            phpCodeForConversion: '@self',
            returnOfPhpExecution: [],
            overridesSelfParam:   false,
        );

        $litNode = new LiteralNode($token, $token->value, 'String');

        $functionNode = new FunctionNode(token: $token);
        $functionNode->method = $method;
        $functionNode->variableBase = $litNode;
        $functionNode->isExternalInstantiation = true;

        $context->addChild($functionNode);
        $parseContext->variables->setVirtualVariable($litNode);

        $parseContext->contextManager->enter(
            new FunctionCallContext($functionNode)
        );
    }
}
