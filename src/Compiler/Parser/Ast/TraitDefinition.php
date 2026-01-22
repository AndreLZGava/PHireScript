<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

class TraitDefinition extends ComplexObjectDefinition
{
    public array $traits = [];
}
