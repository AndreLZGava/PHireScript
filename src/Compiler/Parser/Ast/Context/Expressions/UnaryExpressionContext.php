<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\UnaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\BinaryExpressionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisPropertyAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\GlobalConstantResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NullLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<UnaryExpressionNode>
 */
class UnaryExpressionContext extends AbstractContext
{
    private readonly array $resolvers;

    public function __construct(UnaryExpressionNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new NullLiteralResolver(),
            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new BoolLiteralResolver(),
            new GlobalConstantResolver(),
            new ThisResolver(),
            new ThisPropertyAccessResolver(),
            new VariableReferenceResolver(),
            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),
            new DotResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),
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
            $token->value . ' is not supported in unary expression context!',
            $token->line,
            $token->column,
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        if (empty($this->children)) {
            return false;
        }
        return $token->isEndOfLine() || $token->isComment();
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        if (!empty($this->children)) {
            $this->node->operand = $this->children[0];
        }
        $parseContext->contextManager->current()->addChild($this->node);

        // Walk back so the closing token (EOL/comment) is reprocessed by the parent context
        if ($token->isEndOfLine() || $token->isComment()) {
            $parseContext->tokenManager->walk(-1);
        }
    }
}
