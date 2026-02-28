<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class ArrayMethods extends GeneralType
{
    public function __construct(
        public array $typesKey = [],
        public array $typesValues = [],
    ) {
    }

    public function contains()
    {
        return new BaseMethods(
            phpCodeForConversion: 'in_array(@searching, @self)',
            typesOfReturningMethodInPhireScript: ['Bool'],
            allowedTypesOfParams: ['String', 'Bool', 'Array', 'Number'],
            params: [
                new BaseParams(name: '@searching', type: 'mixed', required: true)
            ]
        );
    }

    public function add()
    {
        return new BaseMethods(
            phpCodeForConversion: '@self[@key] = @value',
            typesOfReturningMethodInPhireScript: [],
            allowedTypesOfParams: [],
            params: [
                new BaseParams(name: '@value', type: 'mixed', required: true),
                new BaseParams(name: '@key', type: 'string', required: true, defaultValue: null),
            ]
        );
    }
}
