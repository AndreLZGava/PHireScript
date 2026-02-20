<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Expressions;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\Types\QueueContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\ConsumptionParams\ClosingParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\ConsumptionParams\OpeningParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\QueueResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class MethodConsumptionContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(public Node $node)
    {
        $this->resolvers = [
            new OpeningParenthesisResolver(),
            new ClosingParenthesisResolver(),
            new EndOfLineResolver(),
            new DotResolver(),
            new FunctionCallResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->node->params = $this->children[0];
                return null;
            }
        }
        throw new \Exception($token->value . ' is not supported in method consumption context!');
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        if ($token->isEndOfLine()) {
            $parseContext->contextManager->exit();
        }
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        /**
         * myTest.myFunction().myAnotherFunction()
         * .myAnotherFunction().
         * myNewMethod()
         */
        return $token->value == '.' || $token->isEndOfLine();// $parseContext->tokenManager->getPreviousTokenBeforeCurrent()->value === ')';
    }
}
