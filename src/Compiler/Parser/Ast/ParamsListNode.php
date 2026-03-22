<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class ParamsListNode extends Node
{
    public function __construct(public Token $token, public array $params = [])
    {
    }
}
