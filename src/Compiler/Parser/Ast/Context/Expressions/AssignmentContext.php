<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Expressions;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ExternalClassAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ExternalMethodCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisPropertyAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ArrayLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ArrayResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\PrimitiveCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ListResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\MapResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NullLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ObjectLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\QueueResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StackResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\MetaTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\PrimitiveResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\SuperTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\SafeNavigationResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ArrowFunctionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\BinaryExpressionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\GlobalConstantResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\OpeningParenthesisResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\UnaryNegationResolver;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class AssignmentContext extends AbstractContext
{
    private readonly array $resolvers;
    public bool $assignmentContext = true;

    public function __construct(AssignmentNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new AssignmentResolver(),

            new QueueResolver(),
            new StackResolver(),
            new MapResolver(),
            new ListResolver(),
            new PrimitiveCastingResolver(),
            new ArrayResolver(),

            new ExternalClassAccessResolver(),
            new ExternalMethodCallResolver(),

            new GlobalConstantResolver(),
            new NullLiteralResolver(),
            new StringLiteralResolver(),
            new NumberLiteralResolver(),
            new ArrayLiteralResolver(),
            new BoolLiteralResolver(),
            new ObjectLiteralResolver(),

            new ThisResolver(),
            new ThisPropertyAccessResolver(),
            new VariableReferenceResolver(),
            new BinaryExpressionResolver(),

            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),

            new TypeResolver(),
            new PrimitiveResolver(),

            new SuperTypeCastingResolver(),
            new MetaTypeCastingResolver(),

            new OpeningParenthesisResolver(),
            new UnaryNegationResolver(),

            new DotResolver(),
            new SafeNavigationResolver(),
            new EndOfLineResolver(),
            new CommentResolver(),

            new VariableConsumptionResolver(),

            new ArrowFunctionResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);

                if (!($resolver instanceof CommentResolver)) {
                    $lastChild = !empty($this->children) ? end($this->children) : null;
                    $this->node->right = $lastChild;
                    if (property_exists($this->node->left, 'value')) {
                        $this->node->left->value = $lastChild;
                    }
                    if (
                        property_exists($this->node->left, 'type') &&
                        !($this->node->left instanceof PropertyAccessNode)
                    ) {
                        $this->node->left->type = $lastChild;
                    }
                }

                // When the right side is an external class access, register the left variable as external type
                if (
                    $resolver instanceof ExternalClassAccessResolver ||
                    $resolver instanceof ExternalMethodCallResolver
                ) {
                    $focus = $parseContext->variables->getVariableOnFocus();
                    if ($focus instanceof \PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode) {
                        $varName = $this->node->left->name ?? null;
                        if ($varName !== null) {
                            $parseContext->registerExternalVarType($varName, $focus->value);
                        }
                    }
                }

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
        if ($token->isEndOfLine()) {
            $next = $parseContext->tokenManager->getNextTokenAfterCurrent();
            // Multi-line chain: EOL followed by . or ?. continues the chain
            if ($next->isDot() || $next->isSafeNavigation()) {
                return false;
            }
            return true;
        }
        return $token->isComment();
    }
}
