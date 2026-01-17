<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class ClassDefinition extends Node
{
    public string $type;
    public string $name;
    public bool $readOnly = false;
    public array $modifiers = [];
    public ?string $docBlock = null;
    public ?string $extends = null;
    public array $mixins = [];
    public array $implements = [];
    public array $body = [];
}
