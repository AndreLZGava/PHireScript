<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Expressions;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\CastResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\QueueResolver ;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class AssignmentContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(public Node $node)
    {
        $this->resolvers = [
            new AssignmentResolver(),
            new QueueResolver(),
            new CastResolver(),
            new StringLiteralResolver(),
            new BoolLiteralResolver(),
            new VariableReferenceResolver(),
            new DotResolver(),
            new FunctionCallResolver(),
            new EndOfLineResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->node->right = $this->children[0];
                $this->node->left->value = $this->children[0];
                $this->node->left->type = $this->children[0];
                return null;
            }
        }
        throw new \Exception($token->value . ' is not supported in assignment context!');
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }
}
