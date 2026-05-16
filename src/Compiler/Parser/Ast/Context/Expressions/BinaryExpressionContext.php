<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BinaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ComparisonExpressionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NullLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Signatures\ClosingParamsDeclarationResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<BinaryExpressionNode>
 */
class BinaryExpressionContext extends AbstractContext
{
    private const LOGICAL_OPERATORS = ['&&', '||'];
    private const COMPARISON_OPERATORS = ['>', '<', '==', '===', '!=', '!==', '>=', '<='];
    private const VALUE_TYPES = ['T_NUMBER', 'T_STRING_LIT', 'T_BOOL', 'T_NULL', 'T_IDENTIFIER'];

    private readonly array $resolvers;

    public function __construct(BinaryExpressionNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new NumberLiteralResolver(),
            new StringLiteralResolver(),
            new BoolLiteralResolver(),
            new NullLiteralResolver(),
            new VariableReferenceResolver(),
            new ComparisonExpressionResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),
            new ClosingParamsDeclarationResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        if (in_array($token->value, self::LOGICAL_OPERATORS, true)) {
            if ($this->node->right === null && !empty($this->children)) {
                $this->node->right = array_shift($this->children);
                $this->children = [];
            }
            $this->node = new BinaryExpressionNode($this->node, $token->value, null);
            $this->children = [];
            return null;
        }

        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);
                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in binary expression context!',
            $token->line,
            $token->column,
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
        $nextValue = $next->value;

        if (in_array($nextValue, self::LOGICAL_OPERATORS, true)) {
            return false;
        }

        if (in_array($nextValue, self::COMPARISON_OPERATORS, true)) {
            return false;
        }

        if ($this->node->right === null) {
            if (!empty($this->children)) {
                $this->node->right = array_shift($this->children);
                $this->children = [];
            } elseif ($parseContext->peekPrevious() !== null) {
                $this->node->right = $parseContext->consumePrevious();
            }
        }

        return $this->node->right !== null;
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $parseContext->contextManager->current()->addChild($this->node);

        if (!in_array($token->type, self::VALUE_TYPES, true)) {
            $parseContext->tokenManager->walk(-1);
        }
    }
}
