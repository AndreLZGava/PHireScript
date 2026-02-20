<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class FunctionNode extends Node
{
    public mixed $variableBase;
    public BaseMethods $method;
    public ?ParamsNode $params = null;
}
