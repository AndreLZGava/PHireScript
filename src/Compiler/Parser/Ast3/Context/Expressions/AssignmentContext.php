<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Expressions;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ArrayLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\CastResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ListResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\MapResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ObjectLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\QueueResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\StackResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class AssignmentContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(AssignmentNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new AssignmentResolver(),

            new QueueResolver(),
            new StackResolver(),
            new MapResolver(),
            new ListResolver(),
            new CastResolver(),

            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new ArrayLiteralResolver(),
            new BoolLiteralResolver(),
            new ObjectLiteralResolver(),

            new VariableReferenceResolver(),

            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),

            new DotResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),

            new VariableConsumptionResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->node->right = $this->getChildrenValues();
                $this->node->left->value = $this->getChildrenValues();
                $this->node->left->type = $this->getChildrenValues();
                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in assignment context!',
            $token->line,
            $token->column
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine() || $token->isComment();
    }
}
