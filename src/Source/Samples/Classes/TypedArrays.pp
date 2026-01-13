<?php

namespace PHPScript;

use PHPScript\Runtime\Types\TypeGuard;

class TypedArrays {

    public function testSimpleArray(): array {
        return [];
    }

    public function testPrimitiveArray(string $test): array {
        return TypeGuard::validateArray([1, 15.2, 'test'], ['Int', 'Float', 'String']);
    }

}
