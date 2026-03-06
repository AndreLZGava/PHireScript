<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods;

class BaseMethods
{
    public function __construct(
        public string $name,
        public string $phpCodeForConversion,
        public array $returnOfPhpExecution,
        public array $subTypes = [],
        public array $params = [],
        public array $functionsValidate = [],
        public bool $isMutable = false,
        public ?array $injections = [],
        public ?BaseRegistryFunctions $function = null,
    ) {
    }
}
