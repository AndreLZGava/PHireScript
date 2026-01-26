<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

abstract class Node
{
    public int $line;
    public int $column;
    public function __construct(Token $token)
    {
        $this->line = $token->line;
        $this->column = $token->column;
    }
}
