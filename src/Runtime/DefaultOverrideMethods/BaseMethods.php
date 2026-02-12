<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods;

class BaseMethods
{
    public function __construct(
        public string $phpCodeForConversion,
        public array $typesOfReturningMethodInPhireScript,
        public array $allowedTypesOfParams = [],
        public array $params = [],
    ) {
    }
}
