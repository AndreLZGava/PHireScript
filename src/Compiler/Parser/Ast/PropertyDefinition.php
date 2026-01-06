<?php

namespace PHPScript\Compiler\Parser\Ast;

class PropertyDefinition extends Node
{
    public string $name;
    public ?string $type = null;
    public array $modifiers = [];
    public ?string $defaultValue = null;
    public array $resolvedTypeInfo = [];
}
