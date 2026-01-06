<?php

namespace PHPScript\Compiler\Parser\Ast;

class ClassDefinition extends Node
{
    public string $type;
    public string $name;
    public ?string $docBlock;
    public ?string $extends = null;
    public array $mixins = [];
    public array $implements = [];
    public array $body = [];
}
