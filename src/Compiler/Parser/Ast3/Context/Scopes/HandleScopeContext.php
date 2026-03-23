<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Scopes;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\MethodDeclarationResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\PropertyResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\VariableResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallNotFoundResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ModifiersResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\MethodScopeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\TypeResolver as TypesTypeResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ClosingCurlyBracketResolver as RootClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\MetaTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\PrimitiveResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\SuperTypeCastingResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\IfResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\ReturnResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\TryResolver;
use PHireScript\Compiler\Parser\Ast\HandleNode;
use PHireScript\Compiler\Parser\Ast\TryScopeNode;

/**
 * @extends AbstractContext<ParamsNode>
 */
class HandleScopeContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(HandleNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new CommentResolver(),
            new EndOfLineResolver(),
            new DotResolver(),
            new VariableResolver(),
            new VariableConsumptionResolver(),
            new AssignmentResolver(),
            new FunctionCallResolver(),
            new FunctionCallNotFoundResolver(),

            new TypesTypeResolver(),
            new PrimitiveResolver(),
            new SuperTypeCastingResolver(),
            new MetaTypeCastingResolver(),

            new IfResolver(),
            new TryResolver(),

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
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->handleClassProperties($token, $keyResolver);

                return null;
            }
        }

        throw new CompileException(
            $token->value . ' is not supported in handle scope definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleClassProperties(Token $token, int|string $keyResolver): void
    {
        $this->node->children = $this->children;
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        if ($token->isClosingCurlyBracket()) {
            $parseContext->contextManager->exit();
        }
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isClosingCurlyBracket();
    }
}
