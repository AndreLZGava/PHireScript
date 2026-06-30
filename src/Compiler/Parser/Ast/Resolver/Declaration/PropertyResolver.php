<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Declaration;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\PropertyDeclarationContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

class PropertyResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $parseContext->tokenManager
            ->sequence()
            ->lookAhead()
            ->separated(
                match: fn ($t) => $t->isType(),
                separator: fn ($t) => $t->isPipe()
            )
            ->once(fn ($t) => $t->isIdentifier())
            ->until(fn ($t) => $t->isEndOfLine())
            ->match();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $accumulated = $parseContext->consumePrevious();
        $accumulated = is_array($accumulated) ? $accumulated : [];

        [$propertyVis, $getterVis, $setterVis, $extraModifiers] = $this->parseAccessors($accumulated);

        $modifiers = array_merge(
            $propertyVis ? [$propertyVis] : ['public'],
            $extraModifiers
        );

        $property = new PropertyNode(
            token: $token,
            types: [$token->value],
            modifiers: $modifiers,
            getter: $getterVis,
            setter: $setterVis,
        );

        $parseContext->contextManager->enter(
            new PropertyDeclarationContext($property, $parseContext->variables)
        );

        $parseContext->definePrevious($property);
        $context->addChild($property);
    }

    /**
     * Parses the accumulated modifier/accessor list and returns [propertyVis, getterVis, setterVis, extraModifiers].
     *
     * Each entry is either a visibility symbol (#/+/*), a plain accessor (</>),
     * a combined T_ACCESSORS token (#<, +>, <>, etc.), or an extra modifier (abstract, readonly, static).
     *
     * @param array<mixed> $accumulated
     * @return array{0: string, 1: string|null, 2: string|null, 3: string[]}
     */
    private function parseAccessors(array $accumulated): array
    {
        $visMap = ['#' => 'private', '+' => 'protected', '*' => 'public'];

        $pendingVis     = null;
        $getterVis      = null;
        $setterVis      = null;
        $extraModifiers = [];
        $extraKeywords  = ['abstract', 'readonly', 'static'];

        foreach ($accumulated as $rawToken) {
            if (!\is_string($rawToken)) {
                continue;
            }
            $token = $rawToken;
            if ($token === '<') {
                $getterVis  = $pendingVis ?? 'public';
                $pendingVis = null;
                continue;
            }

            if ($token === '>') {
                $setterVis  = $pendingVis ?? 'public';
                $pendingVis = null;
                continue;
            }

            // Combined T_ACCESSORS tokens: #<, +<, *<, #>, +>, *>, <>, ><
            if (\str_contains($token, '<') && \str_contains($token, '>')) {
                // both getter and setter
                $prefix     = $visMap[$token[0]] ?? null;
                $getterVis  = $prefix ?? $pendingVis ?? 'public';
                $setterVis  = 'public';
                $pendingVis = null;
                continue;
            }

            if (\str_ends_with($token, '<')) {
                $prefix     = $visMap[$token[0]] ?? null;
                $getterVis  = $prefix ?? $pendingVis ?? 'public';
                $pendingVis = null;
                continue;
            }

            if (\str_ends_with($token, '>')) {
                $prefix     = $visMap[$token[0]] ?? null;
                $setterVis  = $prefix ?? $pendingVis ?? 'public';
                $pendingVis = null;
                continue;
            }

            // Plain visibility modifier
            if (isset($visMap[$token])) {
                $pendingVis = $visMap[$token];
                continue;
            }

            // Long-form keywords (public/protected/private)
            if (\in_array($token, ['public', 'protected', 'private'], true)) {
                $pendingVis = $token;
                continue;
            }

            // Extra modifiers (abstract, readonly, static)
            if (\in_array($token, $extraKeywords, true)) {
                $extraModifiers[] = $token;
            }
        }

        $propertyVis = $pendingVis ?? 'public';

        return [$propertyVis, $getterVis, $setterVis, $extraModifiers];
    }
}
