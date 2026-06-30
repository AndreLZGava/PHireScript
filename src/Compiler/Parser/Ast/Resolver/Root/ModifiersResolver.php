<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Root;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Root\ExternalContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExternalNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class ModifiersResolver implements ContextTokenResolver
{
    public const MODIFIERS = [
    '*',
    '#',
    '+',
    '<',
    '>',
    'public',
    'protected',
    'private',
    'abstract',
    'readonly',
    'static'
    ];

    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return \in_array($token->value, self::MODIFIERS, true) || $token->isAccessor();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {

        $previousModifiers = $parseContext->consumePrevious();
        if (!is_array($previousModifiers)) {
            $previousModifiers = [];
        }
        $previousModifiers[] = $token->value;
        $parseContext->definePrevious($previousModifiers);
    }

    /**
     * @param string[] $previousModifiers
     * @return string[]
     */
    public static function getModifiers(array $previousModifiers): array
    {
        return array_values(array_filter(
            $previousModifiers,
            fn ($item) => \in_array($item, self::MODIFIERS, true)
        ));
    }
}
