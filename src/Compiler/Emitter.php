<?php

declare(strict_types=1);

namespace PHPScript\Compiler;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\EmitterDispatcher;
use PHPScript\Compiler\Emitter\NodeEmitters\ArrayLiteralEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\AssignmentEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\PropertyEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ClassEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\DependencyEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\GlobalStatementEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\InterfaceEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\LiteralEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\MethodEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\PackageEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ParameterEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ProgramEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\PropertyAccessEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\PropertyDeclarationEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ReturnEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ScalarLiteralEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\ThisExpressionEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\VariableEmitter;
use PHPScript\Compiler\Emitter\NodeEmitters\VoidExpressionEmitter;
use PHPScript\Compiler\Emitter\Type\PhpTypeResolver;
use PHPScript\Compiler\Emitter\UseRegistry;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\DependencyGraphBuilder;

class Emitter
{
    private readonly EmitterDispatcher $dispatcher;

    public function __construct(private array $config, private DependencyGraphBuilder $dependencyManager)
    {
        $this->dispatcher = new EmitterDispatcher([
            new ProgramEmitter(),
            new PackageEmitter(),
            new DependencyEmitter(),
            new InterfaceEmitter(),
            new ClassEmitter(),
            new MethodEmitter(),

            new ReturnEmitter(),
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
