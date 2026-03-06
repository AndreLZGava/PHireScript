<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types;

use Exception;
use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ClosingAngleBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\OpeningAngleBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\PipeResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StackNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class StackContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(StackNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new OpeningAngleBracketResolver(),
            new TypeResolver(),
            new ClosingAngleBracketResolver(),
            new EndOfLineResolver(),
            new PipeResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $parseContext->contextManager->current()->addChild($this->getChildrenValues());

                $this->node->types = array_unique($parseContext->contextManager->current()->children);

                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in stack definition context!',
            $token->line,
            $token->column,
        );
    }

    public function validation(Token $token, ParseContext $parseContext): void
    {
        if (
            ($token->isEndOfLine() || $token->value === '>') &&
            count($this->node->types) === 0
        ) {
            throw new CompileException(
                'Stack must define at least one subtype!',
                $token->line,
                $token->column
            );
        }
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->value === '>';
    }
}
