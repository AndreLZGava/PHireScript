<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Declarations;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NamedArgNode;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\CommaResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ConsumptionParams\ClosingParamsConsumptionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ExternalClassAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ExternalMethodCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ExternalPropertyAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\IgnoreColonResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ArrayLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<NamedArgNode>
 */
class NamedArgContext extends AbstractContext
{
    private readonly array $resolvers;

    public function __construct(NamedArgNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new IgnoreColonResolver(),
            new ClosingParamsConsumptionResolver(),
            new CommaResolver(),
            new ExternalClassAccessResolver(),
            new ExternalMethodCallResolver(),
            new ExternalPropertyAccessResolver(),
            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new BoolLiteralResolver(),
            new ArrayLiteralResolver(),
            new VariableReferenceResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);
                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported as a named argument value!',
            $token->line,
            $token->column,
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isComma() || $token->isClosingParenthesis();
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $value = end($this->children);
        if ($value !== false) {
            $this->node->value = $value;
        }
    }
}
