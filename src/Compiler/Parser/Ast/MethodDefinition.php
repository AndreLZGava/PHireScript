<?php

namespace PHPScript\Compiler\Parser\Ast;

class MethodDefinition extends Node {
    public string $name;
    public array $modifiers = [];
    public array $args = [];
    public ?string $returnType = null;
    public string $bodyCode;
}
