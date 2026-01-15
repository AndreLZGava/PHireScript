<?php

declare(strict_types=1);

namespace PHPScript;

use PHPScript\Compiler\DependencyGraphBuilder\DependencyTree\Parser;
use PHPScript\Compiler\Program;
use PHPScript\Compiler\Scanner;

class TranspilerDependencyTree
{
    public function __construct(private readonly array $config)
    {
    }

    public function compile(string $code, string $path): Program
    {
        $scanner = new Scanner($code);
        $tokens = $scanner->tokenize();

        $parser = new Parser($this->config);
        $ast = $parser->parse($tokens, $path);

        return $ast;
    }
}
