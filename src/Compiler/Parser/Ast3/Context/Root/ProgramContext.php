<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Root;

use Exception;
use PHireScript\Compiler\Emitter\NodeEmitters\AssignmentEmitter;
use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\VariableConsumptionResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Declaration\VariableResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\FunctionCallResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class ProgramContext extends AbstractContext
{
    private array $resolvers = [];

    public function __construct(ParseContext $parseContext)
    {
        $this->resolvers = [
            new CommentResolver(),
            new EndOfLineResolver(),
            new DotResolver(),
            new VariableResolver(),
            new VariableConsumptionResolver(),
            new AssignmentResolver(),
            new FunctionCallResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {

        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $parseContext->program->statements = $this->children;
                return $parseContext->program;
            }
        }
        Debug::show($token, $parseContext->tokenManager->getLeftTokens(5));
        throw new CompileException(
            $token->value . ' is not supported in program context!',
            $token->line,
            $token->column
        );
    }
}
