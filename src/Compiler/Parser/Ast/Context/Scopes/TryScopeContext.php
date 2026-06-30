<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Scopes;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\MethodDeclarationResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\PropertyResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\VariableResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\BinaryExpressionResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisPropertyAccessResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\ThisResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\MethodScopeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\TypeResolver as TypesTypeResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\ClosingCurlyBracketResolver as RootClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\MetaTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\PrimitiveResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\SuperTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\IfResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\LoopResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\ReturnResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\SwitchResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\TryResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\TryScopeNode;

/**
 * @extends AbstractContext<ParamsNode>
 */
class TryScopeContext extends AbstractContext
{
    private readonly array $resolvers;

    public function __construct(TryScopeNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new CommentResolver(),
            new EndOfLineResolver(),
            new ThisResolver(),
            new DotResolver(),
            new ThisPropertyAccessResolver(),
            new VariableResolver(),
            new VariableConsumptionResolver(),
            new AssignmentResolver(),
            new IfResolver(),
            new LoopResolver(),
            new SwitchResolver(),
            new TryResolver(),
            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),
            new BinaryExpressionResolver(),

            new TypesTypeResolver(),
            new PrimitiveResolver(),
            new SuperTypeCastingResolver(),
            new MetaTypeCastingResolver(),

            // for closing
            new RootClosingCurlyBracketResolver(),
            // this only for methods and functions
            new ReturnResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {

        foreach ($this->resolvers as $keyResolver => $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $resolver::class;
                $resolver->resolve($token, $parseContext, $this);
                $this->handleClassProperties($token, $keyResolver);

                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in try scope definition context!',
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
