<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Parser\Ast2\Expression\Assignment;
use PHireScript\Compiler\Parser\Ast2\Expression\GenericType;
use PHireScript\Compiler\Parser\Ast2\Types\ListValue;
use PHireScript\Compiler\Parser\Ast2\Types\MapValue;
use PHireScript\Compiler\Parser\Ast2\Types\QueueValue;
use PHireScript\Compiler\Parser\Ast2\Types\StackValue;
use PHireScript\Compiler\Parser\Ast2\Statement\Comment;
use PHireScript\Compiler\Parser\Ast2\Statement\EndOfLine;
use PHireScript\Compiler\Parser\Ast2\Statement\LeftWingTyping;
use PHireScript\Compiler\Parser\Ast2\Statement\Pipe;
use PHireScript\Compiler\Parser\Ast2\Statement\RightWingTyping;
use PHireScript\Compiler\Parser\Ast2\Statement\Variable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Parser\ParserDispatcher;
use PHireScript\Runtime\RuntimeClass;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Context\ContextState;
use PHireScript\Compiler\Parser\Managers\ContextManager;
use PHireScript\Compiler\Program;

class Parser
{
    private array $factories;
    private readonly ParserDispatcher $parserDispatcher;

    public function __construct(private readonly array $config)
    {
        $this->factories = FactoryInitializer::getFactories();
        $this->parserDispatcher = new ParserDispatcher([
            new Variable(),
            new Assignment(),
            new Comment(),
            new QueueValue(),
            new StackValue(),
            new ListValue(),
            new MapValue(),
            new LeftWingTyping(),
            new Pipe(),
            new GenericType(),
            new RightWingTyping(),
            new EndOfLine(),

            /*
            new StringLiteralValue(),
            new BoolLiteralValue(),
            new NumberLiteralValue(),
            new ObjectArrayLiteralValue(),
            new VariableLiteralReference(),
            new StringCastValue(),

            //
            new ArrayCastVariable(),
            new BlockBrackets(),
            new BlockBracketsCommaOnMethod(),
            new BlockParenthesisOnMethod(),
            new BoolCastVariable(),
            new CharactersOnMethods(),
            new ComplexObject(),
            new DotAsPointer(),
            new DotOnGeneral(),
            new FloatCastVariable(),
            new GetterAndSetters(),
            new GettingArguments(),
            new IntCastVariable(),
            new ObjectCastVariable(),
            new SingleCommaOnClass(),
            new SingleOpenParenthesisOperator(),
            new SuperTypeCastVariable(),
            new VariableAssignmentFactory(),*/

        ]);
    }

    public function parse($tokens, $path): Program
    {
        $tokenManager = new TokenManager(RuntimeClass::CONTEXT_GENERAL, $tokens, 0);
        $program = new Program($tokenManager->getCurrentToken());
        $context = new ContextManager(new ContextState(Context::Global, $program));
        $context->path = $path;
        $context->config = $this->config;

        $parseContext = new ParseContext(
            variables: new VariableManager(),
            program: $program,
            emitter: $this->parserDispatcher,
            tokenManager: $tokenManager,
            context: $context,
        );

        while (!$tokenManager->isEndOfTokens()) {
            $token = $tokenManager->getCurrentToken();
            $result = $this->parserDispatcher->emit($token, $parseContext);
            if ($result) {
                $program->statements[] = $result;
            }
        }

        return $program;
    }
}
