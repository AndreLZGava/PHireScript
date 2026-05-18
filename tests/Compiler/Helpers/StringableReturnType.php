<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Helpers;

use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ReturnTypeNode;

/**
 * ReturnTypeNode backed by a plain type string, populating the $types array
 * so MethodReturnChecker can read ->types without a real token.
 */
class StringableReturnType extends ReturnTypeNode
{
    public function __construct(string $typeString)
    {
        $this->types = \explode('|', $typeString);
    }
}
