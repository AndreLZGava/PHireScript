<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class TraitDefinition extends Node
{
    public string $type;
    public string $name;
    public array $body = [];
}
