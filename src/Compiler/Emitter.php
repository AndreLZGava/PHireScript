<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\EmitterDispatcher;
use PHireScript\Compiler\Emitter\NodeEmitters\ArrayLiteralEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\AssignmentEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\PropertyEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ClassEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\DependencyEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ExternalEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\GlobalStatementEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\InterfaceEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\KeyValuePairEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\LiteralEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\MethodEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ObjectLiteralEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\PackageEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ParameterEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ProgramEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\PropertyAccessEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\PropertyDeclarationEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ReturnEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ScalarLiteralEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\ThisExpressionEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\VariableEmitter;
use PHireScript\Compiler\Emitter\NodeEmitters\VoidExpressionEmitter;
use PHireScript\Compiler\Emitter\Type\PhpTypeResolver;
use PHireScript\Compiler\Emitter\UseRegistry;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\DependencyGraphBuilder;

class Emitter
{
    private readonly EmitterDispatcher $dispatcher;

    public function __construct(private array $config, private DependencyGraphBuilder $dependencyManager)
    {
        $this->dispatcher = new EmitterDispatcher([
            new ProgramEmitter(),
            new PackageEmitter(),
            new DependencyEmitter(),
            new ExternalEmitter(),
            new InterfaceEmitter(),
            new ClassEmitter(),
            new MethodEmitter(),

            new ObjectLiteralEmitter(),

            new ReturnEmitter(),
            new KeyValuePairEmitter(),
            new ArrayLiteralEmitter(),
            new LiteralEmitter(),

            new PropertyDeclarationEmitter(),
            new VoidExpressionEmitter(),
            new VariableEmitter(),
            new AssignmentEmitter(),
            new ThisExpressionEmitter(),
            new PropertyAccessEmitter(),

            new ParameterEmitter(),
            new PropertyEmitter(),
            new GlobalStatementEmitter(),
            //new ScalarLiteralEmitter(),
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
        );

        return $this->dispatcher->emit($program, $context);
    }
}
