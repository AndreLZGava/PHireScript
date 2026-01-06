<?php

namespace PHPScript\Compiler;

use PHPScript\Compiler\Parser\Ast\Node;

class Program extends Node {
    public array $statements = [];
}
