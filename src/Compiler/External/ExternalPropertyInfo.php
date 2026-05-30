<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalPropertyInfo
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $type,
    ) {
    }
}
