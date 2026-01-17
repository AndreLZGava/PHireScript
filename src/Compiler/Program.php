<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Parser\Ast\Node;

class Program extends Node
{
    public array $statements = [];
    public string $path = '';
    public array $config = [];
}
