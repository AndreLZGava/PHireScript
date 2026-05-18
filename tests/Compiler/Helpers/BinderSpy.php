<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Helpers;

use PHireScript\Compiler\Binder;
use PHireScript\Compiler\Binder\Declaration\PropertyTypeResolutionBinder;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;

/**
 * Exposes type-resolution helpers so tests can drive them without going through
 * the full bind() pipeline. Delegates to PropertyTypeResolutionBinder internally.
 */
class BinderSpy extends Binder
{
    public function publicCategorizeType(string $typeName): array
    {
        $prop = new PropertyNode(new Token('T_IDENTIFIER', 'spy', 1, 1), [$typeName], 'spy');
        $this->publicResolvePropertyTypes($prop);
        return $prop->resolvedTypeInfo[0];
    }

    public function publicResolvePropertyTypes(PropertyNode|ParamArgumentNode $prop): void
    {
        (new PropertyTypeResolutionBinder())->bind($prop, $this);
    }
}
