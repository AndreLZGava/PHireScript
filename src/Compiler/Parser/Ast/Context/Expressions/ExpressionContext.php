<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\BinaryExpressionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\GlobalConstantResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ParenGroupResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisPropertyAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ArrayLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ArrayResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ListResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\MapResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NullLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ObjectLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\PrimitiveCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\QueueResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StackResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\UnaryNegationResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\MetaTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\PrimitiveResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\SuperTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\SafeNavigationResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

class ExpressionContext extends AbstractContext
{
    public int $parenDepth = 0;

    private readonly array $resolvers;

    public function __construct(Node $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new NullLiteralResolver(),
            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new BoolLiteralResolver(),
            new ArrayLiteralResolver(),
            new ObjectLiteralResolver(),
            new PrimitiveCastingResolver(),
            new ArrayResolver(),
            new QueueResolver(),
            new StackResolver(),
            new MapResolver(),
            new ListResolver(),
            new GlobalConstantResolver(),

            new VariableReferenceResolver(),
            new ThisResolver(),
            new ThisPropertyAccessResolver(),

            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),
            new DotResolver(),
            new SafeNavigationResolver(),

            new BinaryExpressionResolver(),
            new UnaryNegationResolver(),
            new ParenGroupResolver(),

            new SuperTypeCastingResolver(),
            new MetaTypeCastingResolver(),
            new TypeResolver(),
            new PrimitiveResolver(),

            new AssignmentResolver(),
            new VariableConsumptionResolver(),
            new CommentResolver(),
            new EndOfLineResolver(),
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
            $token->value . ' is not supported in expression context!',
            $token->line,
            $token->column,
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        if ($this->parenDepth > 0) {
            if ($token->isClosingParenthesis()) {
                $this->parenDepth--;
                return $this->parenDepth === 0;
            }
            return false;
        }

        if ($token->isEndOfLine() || $token->isComment()) {
            $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
            if ($next->isDot() || $next->isSafeNavigation()) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $result = !empty($this->children) ? end($this->children) : null;
        if ($result !== null) {
            $parseContext->contextManager->current()->addChild($result);
        }
    }
}
