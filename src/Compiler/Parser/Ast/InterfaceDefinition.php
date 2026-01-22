<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class InterfaceDefinition extends ComplexObjectDefinition
{
    public array $modifiers = [];
    public array $extends = [];
}
