<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalConstantInfo
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $value,
    ) {
    }
}
