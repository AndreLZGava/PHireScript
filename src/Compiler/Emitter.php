<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\EmitterDispatcher;
use PHireScript\Compiler\Emitter\Statements\AlwaysEmitter;
use PHireScript\Compiler\Emitter\Statements\HandleEmitter;
use PHireScript\Compiler\Emitter\Statements\IfStatementEmitter;
use PHireScript\Compiler\Emitter\Statements\IssetOperatorEmitter;
use PHireScript\Compiler\Emitter\Statements\NewExceptionEmitter;
use PHireScript\Compiler\Emitter\Statements\NotOperatorEmitter;
use PHireScript\Compiler\Emitter\Statements\ThrowStatementEmitter;
use PHireScript\Compiler\Emitter\Statements\TryEmitter;
use PHireScript\Compiler\Emitter\Expressions\ArrayLiteralEmitter;
use PHireScript\Compiler\Emitter\Declarations\ArrowFunctionEmitter;
use PHireScript\Compiler\Emitter\Statements\AssignmentEmitter;
use PHireScript\Compiler\Emitter\Expressions\BinaryExpressionEmitter;
use PHireScript\Compiler\Emitter\Expressions\GroupedExpressionEmitter;
use PHireScript\Compiler\Emitter\Expressions\UnaryExpressionEmitter;
use PHireScript\Compiler\Emitter\Expressions\BoolEmitter;
use PHireScript\Compiler\Emitter\Expressions\CastingEmitter;
use PHireScript\Compiler\Emitter\OOP\ClassBodyEmitter;
use PHireScript\Compiler\Emitter\OOP\PropertyEmitter;
use PHireScript\Compiler\Emitter\Declarations\ClassEmitter;
use PHireScript\Compiler\Emitter\Statements\CommentStatementEmitter;
use PHireScript\Compiler\Emitter\Declarations\UseEmitter;
use PHireScript\Compiler\Emitter\Declarations\ExternalEmitter;
use PHireScript\Compiler\Emitter\Declarations\FunctionEmitter;
use PHireScript\Compiler\Emitter\Statements\GlobalConstEmitter;
use PHireScript\Compiler\Emitter\Statements\GlobalStatementEmitter;
use PHireScript\Compiler\Emitter\OOP\InterfaceBodyEmitter;
use PHireScript\Compiler\Emitter\Declarations\InterfaceEmitter;
use PHireScript\Compiler\Emitter\OOP\InterfaceMethodEmitter;
use PHireScript\Compiler\Emitter\Expressions\KeyValuePairEmitter;
use PHireScript\Compiler\Emitter\Collections\ListEmitter;
use PHireScript\Compiler\Emitter\Expressions\LiteralEmitter;
use PHireScript\Compiler\Emitter\Collections\MapEmitter;
use PHireScript\Compiler\Emitter\OOP\MethodEmitter;
use PHireScript\Compiler\Emitter\OOP\MethodScopeEmitter;
use PHireScript\Compiler\Emitter\Expressions\NullEmitter;
use PHireScript\Compiler\Emitter\Expressions\NumberEmitter;
use PHireScript\Compiler\Emitter\Expressions\ObjectLiteralEmitter;
use PHireScript\Compiler\Emitter\Declarations\PackageEmitter;
use PHireScript\Compiler\Emitter\Signatures\ParamArgumentEmitter;
use PHireScript\Compiler\Emitter\Signatures\ParameterEmitter;
use PHireScript\Compiler\Emitter\Signatures\ParamsListEmitter;
use PHireScript\Compiler\Emitter\Root\ProgramEmitter;
use PHireScript\Compiler\Emitter\Expressions\PropertyAccessEmitter;
use PHireScript\Compiler\Emitter\OOP\PropertyDeclarationEmitter;
use PHireScript\Compiler\Emitter\Collections\QueueEmitter;
use PHireScript\Compiler\Emitter\Expressions\RangeEmitter;
use PHireScript\Compiler\Emitter\Statements\ReturnEmitter;
use PHireScript\Compiler\Emitter\OOP\ReturnTypeEmitter;
use PHireScript\Compiler\Emitter\Collections\StackEmitter;
use PHireScript\Compiler\Emitter\Statements\VariableReferenceAssignEmitter;
use PHireScript\Compiler\Emitter\Expressions\StringEmitter;
use PHireScript\Compiler\Emitter\Expressions\SuperTypeEmitter;
use PHireScript\Compiler\Emitter\Expressions\ThisExpressionEmitter;
use PHireScript\Compiler\Emitter\Declarations\ExternalCallEmitter;
use PHireScript\Compiler\Emitter\Declarations\TraitEmitter;
use PHireScript\Compiler\Emitter\Statements\VariableDeclarationEmitter;
use PHireScript\Compiler\Emitter\Statements\VariableEmitter;
use PHireScript\Compiler\Emitter\Expressions\VoidExpressionEmitter;
use PHireScript\Compiler\Emitter\OOP\WithEmitter;
use PHireScript\Compiler\Emitter\Base\Type\PhpTypeResolver;
use PHireScript\Compiler\Emitter\Base\UseRegistry;
use PHireScript\DependencyGraphBuilder;
use PHireScript\SymbolTable;

class Emitter
{
    private readonly EmitterDispatcher $dispatcher;

    public function __construct(
        private array $config,
        private readonly DependencyGraphBuilder $dependencyManager,
        private readonly ?SymbolTable $symbolTable = null,
    ) {
        $this->dispatcher = new EmitterDispatcher([
            new ProgramEmitter(),
            new PackageEmitter(),
            new UseEmitter(),
            new ExternalEmitter(),

            new GlobalConstEmitter(),

            new InterfaceEmitter(),
            new InterfaceBodyEmitter(),
            new ClassEmitter(),
            new ClassBodyEmitter(),
            new TraitEmitter(),
            new InterfaceMethodEmitter(),
            new MethodEmitter(),
            new ReturnTypeEmitter(),
            new MethodScopeEmitter(),
            new ExternalCallEmitter(),
            new FunctionEmitter(),
            new WithEmitter(),

            new ObjectLiteralEmitter(),

            new ReturnEmitter(),
            new KeyValuePairEmitter(),
            new ArrayLiteralEmitter(),
            new LiteralEmitter(),
            new NullEmitter(),
            new RangeEmitter(),
            new BoolEmitter(),
            new StringEmitter(),
            new NumberEmitter(),
            new BinaryExpressionEmitter(),
            new GroupedExpressionEmitter(),
            new UnaryExpressionEmitter(),
            new VariableReferenceAssignEmitter(),
            new SuperTypeEmitter(),
            new QueueEmitter(),
            new ListEmitter(),
            new StackEmitter(),
            new MapEmitter(),

            new PropertyDeclarationEmitter(),
            new VoidExpressionEmitter(),
            new VariableEmitter(),
            new VariableDeclarationEmitter(),
            new AssignmentEmitter(),
            new ThisExpressionEmitter(),
            new PropertyAccessEmitter(),

            new ParameterEmitter(),
            new PropertyEmitter(),

            new CommentStatementEmitter(),
            new GlobalStatementEmitter(),
            new IfStatementEmitter(),
            new TryEmitter(),
            new HandleEmitter(),
            new AlwaysEmitter(),
            new IssetOperatorEmitter(),
            new NotOperatorEmitter(),
            new ThrowStatementEmitter(),
            new NewExceptionEmitter(),
            new CastingEmitter(),
            new ParamsListEmitter(),
            new ParamArgumentEmitter(),
            new ArrowFunctionEmitter(),
        ]);
    }

    public function emit(Program $program): string
    {
        $useRegistry = new UseRegistry();

        $context = new EmitContext(
            dev: $this->config['dev'] ?? false,
            uses: $useRegistry,
            emitter: $this->dispatcher,
            types: new PhpTypeResolver($this->config),
            dependencyManager: $this->dependencyManager,
            symbolTable: $this->symbolTable,
        );

        return $this->dispatcher->emit($program, $context);
    }
}
