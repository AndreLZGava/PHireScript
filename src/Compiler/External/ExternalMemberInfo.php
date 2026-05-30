<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalMemberInfo
{
    /**
     * @param string|string[]|null $returnType
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $isStatic,
        public readonly string|array|null $returnType,
        public readonly int $requiredParamCount,
    ) {
    }
}
