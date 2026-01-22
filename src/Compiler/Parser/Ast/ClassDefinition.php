<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class ClassDefinition extends ComplexObjectDefinition
{
    public bool $readOnly = false;
    public array $modifiers = [];
    public ?string $docBlock = null;
    public ?string $extends = null;
    public array $traits = [];
    public array $implements = [];
    public ?ConstructorDefinition $construct = null;
    public array $inject = [];
    public array $cache = [];
    public array $schedule = [];
}
