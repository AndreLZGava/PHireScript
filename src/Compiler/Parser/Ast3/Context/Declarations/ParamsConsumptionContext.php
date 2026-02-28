<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Declarations;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\ConsumptionParams\ClosingParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CommaResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\ConsumptionParams\OpeningParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ArrayLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\QueueResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class ParamsConsumptionContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(public Node $node)
    {
        $this->resolvers = [
            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new BoolLiteralResolver(),
            new ArrayLiteralResolver(),
            new VariableReferenceResolver(),

            new ClosingParenthesisResolver(),
            new CommaResolver(),

            new EndOfLineResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->node->params = $this->children;
                return null;
            }
        }
        // Debug::show($parseContext->tokenManager->getProcessedTokens(10));exit;
        throw new CompileException(
            $token->value . ' is not supported in params context!',
            $token->line,
            $token->column
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->value === ')';
    }
}
