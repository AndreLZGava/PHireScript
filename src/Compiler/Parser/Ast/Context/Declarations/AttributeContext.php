<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Declarations;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\Class\ClassBodyResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<AttributeNode>
 */
class AttributeContext extends AbstractContext
{
    private readonly array $resolvers;

    public function __construct(AttributeNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            'name'   => new IdentifierResolver(),
            'body[]' => new ClassBodyResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $keyResolver => $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);
                $this->handleProperties($keyResolver);
                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in attribute definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleProperties(int|string $keyResolver): void
    {
        if (\is_int($keyResolver)) {
            return;
        }
        $key = $this->sanitizeKeys($keyResolver);
        $value = $this->getChildrenValues($keyResolver);
        $this->node->$key = $value ?: null;
        $this->children = [];
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isClosingCurlyBracket();
    }
}
