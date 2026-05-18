<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Binder\Declaration\ClassBodyBinder;
use PHireScript\Compiler\Binder\Declaration\ClassBinder;
use PHireScript\Compiler\Binder\Declaration\Class\MagicMethodDeclarationBinder;
use PHireScript\Compiler\Binder\Declaration\Class\MethodParamResolutionBinder;
use PHireScript\Compiler\Binder\Declaration\Interface\MethodDeclarationBinder;
use PHireScript\Compiler\Binder\Declaration\PropertyBinder;
use PHireScript\Compiler\Binder\Declaration\InterfaceBinder;
use PHireScript\Compiler\Binder\Declaration\PropertyTypeResolutionBinder;
use PHireScript\Compiler\Binder\Root\ProgramBinder;
use PHireScript\Compiler\Binder\Root\TypeRegistrationBinder;
use PHireScript\Compiler\Binder\Signatures\ModifiersBinder;
use PHireScript\SymbolTable;

class Binder
{
    public Program $program;
    public array $binders = [];

    public function __construct(public readonly SymbolTable $globalTable)
    {
        $this->binders = [
            new TypeRegistrationBinder(),
            new ProgramBinder(),
            new ClassBodyBinder(),
            new InterfaceBinder(),
            new ClassBinder(),
            new MagicMethodDeclarationBinder(),
            new MethodDeclarationBinder(),
            new MethodParamResolutionBinder(),
            new PropertyBinder(),
            new PropertyTypeResolutionBinder(),
            new ModifiersBinder(),
        ];
    }

    public function bind(Program $program)
    {
        $this->program = $program;

        foreach ($this->binders as $bind) {
            if ($bind->mustBind($program)) {
                $bind->bind($program, $this);
            }
        }

        return $program;
    }
}
