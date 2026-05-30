<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalParamInfo
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $type,
        public readonly bool $hasDefault,
    ) {
    }
}
