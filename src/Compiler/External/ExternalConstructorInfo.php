<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalConstructorInfo
{
    /**
     * @param ExternalParamInfo[] $requiredParams
     * @param ExternalParamInfo[] $optionalParams
     */
    public function __construct(
        public readonly bool $isPublic,
        public readonly array $requiredParams,
        public readonly array $optionalParams,
    ) {
    }
}
