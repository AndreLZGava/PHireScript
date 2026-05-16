<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ElseIfNode;
use PHireScript\Compiler\Parser\Ast\Resolver\Scopes\ElseIfScopeResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Signatures\OpeningIfConditionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ElseIfNode>
 */
class ElseIfContext extends AbstractContext
{
    private array $resolvers = [];

    public function __construct(ElseIfNode $node)
    {
        parent::__construct($node);

        $this->resolvers = [
            new EndOfLineResolver(),
            'condition' => new OpeningIfConditionResolver(),
            'statements' => new ElseIfScopeResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $keyResolver => $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);
                $this->processResolvers($keyResolver);
                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in elseif declaration context!',
            $token->line,
            $token->column
        );
    }

    private function processResolvers(int|string $keyResolver): void
    {
        if (\is_int($keyResolver)) {
            return;
        }
        $key = $this->sanitizeKeys($keyResolver);
        $value = $this->getChildrenValues($keyResolver);
        $this->node->$key = $value ?: [];
        $this->children = [];
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return false;
    }
}
