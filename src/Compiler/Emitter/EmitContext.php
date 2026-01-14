<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter;

use PHPScript\Compiler\Emitter\Type\PhpTypeResolver;

class EmitContext
{
    public function __construct(
        public readonly bool $dev,
        public readonly UseRegistry $uses,
        public PhpTypeResolver $types,
        public readonly EmitterDispatcher $emitter,
        public bool $insideInterface = false,
        public bool $insideClass = false,
        public bool $insideMethodSignature = false,
        public ?string $currentMethodReturnType = null,
    ) {
    }
}
