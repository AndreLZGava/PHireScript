<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Root\Class;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\Class\ClassBodyContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\ParseContext;

class ClassBodyResolver implements ContextTokenResolver
{
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        return $token->isOpeningCurlyBracket();
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $className   = $context->node->name;
        $extendsName = $context->node->extends->name ?? null;
        $methods     = $this->extractMethodSignatures($parseContext->tokenManager);

        $parseContext->currentClassName                      = $className;
        $parseContext->currentClassMethods                   = $methods;
        $parseContext->classMethodRegistry[$className]       = [
            'methods' => $methods,
            'extends' => $extendsName,
        ];

        $node = new ClassBodyNode(
            token: $token,
            bodyOf: $className,
            type: $token->value,
        );

        $parseContext->contextManager->enter(
            new ClassBodyContext($node)
        );
        $context->addChild($node);
    }

    /**
     * Look-ahead over the remaining tokens to extract method signatures from this class body.
     * Uses peek() only — never advances the cursor.
     *
     * Detects: T_SYMBOL(#) → T_IDENTIFIER(name) → T_SYMBOL('(') → … → ')' → ':' → <type>
     * at brace depth 1 (immediately inside the class body that is about to open).
     *
     * @return array<string, string> methodName → raw return type
     */
    private function extractMethodSignatures(TokenManager $tokenManager): array
    {
        $methods = [];
        $offset  = 1; // peek(0) is the '{' itself; start at 1 (first token inside the class body)
        $depth   = 1; // depth 1 = directly inside the class body

        while (true) {
            $t = $tokenManager->peek($offset);

            if ($t->type === 'T_EOF' || $t->value === '') {
                break;
            }

            if ($t->value === '{') {
                $depth++;
                $offset++;
                continue;
            }

            if ($t->value === '}') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
                $offset++;
                continue;
            }

            // Look for '#' at depth 1 (direct method declarations, not inside method bodies)
            if ($depth === 1 && $t->value === '#') {
                $nameToken       = $tokenManager->peek($offset + 1);
                $afterName       = $tokenManager->peek($offset + 2);

                if ($nameToken->isIdentifier() && $afterName->value === '(') {
                    $methodName = $nameToken->value;

                    // Skip ahead past the parameter list to find '):' and the return type
                    $inner  = $offset + 3;
                    $pDepth = 1;

                    while ($pDepth > 0) {
                        $pt = $tokenManager->peek($inner);
                        if ($pt->type === 'T_EOF' || $pt->value === '') {
                            break 2;
                        }
                        if ($pt->value === '(') {
                            $pDepth++;
                        } elseif ($pt->value === ')') {
                            $pDepth--;
                        }
                        $inner++;
                    }

                    // peek($inner) is now the token after ')'
                    $colon      = $tokenManager->peek($inner);
                    $returnType = $tokenManager->peek($inner + 1);

                    $validTypes = ['T_PRIMITIVE', 'T_SUPER_TYPE', 'T_META_TYPE', 'T_IDENTIFIER'];
                    $hasType    = $returnType->isIdentifier()
                        || ($returnType->type !== 'T_EOF' && \in_array($returnType->type, $validTypes, true));
                    if ($colon->value === ':' && $hasType) {
                        $methods[$methodName] = $returnType->value;
                    }
                }
            }

            $offset++;
        }

        return $methods;
    }
}
