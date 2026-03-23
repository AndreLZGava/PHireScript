<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Scopes;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\OpeningAlwaysScopeResolver;
use PHireScript\Compiler\Parser\Ast\AlwaysNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class AlwaysContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(AlwaysNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
        new OpeningAlwaysScopeResolver(),
        new EndOfLineResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $keyResolver => $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->handleClassProperties($token, $keyResolver);

                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in always definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleClassProperties(Token $token, int|string $keyResolver): void
    {
        $this->node->scope = $this->children[0];
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        if ($token->isClosingCurlyBracket()) {
            $parseContext->contextManager->exit();
        }
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isClosingCurlyBracket() ||
        $parseContext->tokenManager
        ->getNextTokenAfterCurrent()
        ->value === 'always';
    }
}
