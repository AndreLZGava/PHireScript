<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods;

use Closure;

class BaseMethods
{
    public function __construct(
        public string $phpCodeForConversion,
        public array $typesOfReturningMethodInPhireScript,
        public array $allowedTypesOfParams = [],
        public array $params = [],
        public array $functionsValidate = [],
        public ?array $injections = [],
        public ?BaseMethods $child = null,
        public ?BaseRegistryFunctions $function = null,
    ) {
    }
}
