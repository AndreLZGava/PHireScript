<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Declarations;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\Block\OpeningCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\Class\ExtendsResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\Class\ImplementsResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\Class\WithResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\ClassNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class ClassContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(ClassNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            'name' => new IdentifierResolver(),
            'body[]' => new OpeningCurlyBracketResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),
            'extends' => new ExtendsResolver(),
            'with' => new WithResolver(),
            'implements' => new ImplementsResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        $this->handleModifiers($parseContext->consumePrevious());
        foreach ($this->resolvers as $keyResolver => $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->handleClassProperties($token, $keyResolver);

                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in ' . $this->node->token->value . ' definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleModifiers($previousModifiers)
    {
        $modifiers = $previousModifiers ? ModifiersResolver::getModifiers($previousModifiers) : [];
        if (!empty($modifiers)) {
            $this->node->modifiers = $modifiers;
        }
    }

    private function handleClassProperties(Token $token, int|string $keyResolver): void
    {
        if (is_int($keyResolver)) {
            return;
        }
        $key = $this->sanitizeKeys($keyResolver);
        $value = $this->getChildrenValues($keyResolver);
        $this->node->$key =  $value ?: [];
        $this->children = [];
        return;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->value === '}';
    }
}
