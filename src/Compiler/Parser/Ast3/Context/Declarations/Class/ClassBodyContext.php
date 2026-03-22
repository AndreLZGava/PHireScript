<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Declarations\Class;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\MethodDeclarationResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\PropertyResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\ClassBodyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class ClassBodyContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(ClassBodyNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new EndOfLineResolver(),
            new PropertyResolver(),
            new ClosingCurlyBracketResolver(),
            new CommentResolver(),
            new ModifiersResolver(),
            new MethodDeclarationResolver(),
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
            $token->value . ' is not supported in ' . $this->node->bodyOf . ' body definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleClassProperties(Token $token, int|string $keyResolver): void
    {
        $this->node->children = $this->children;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isClosingCurlyBracket();
    }
}
