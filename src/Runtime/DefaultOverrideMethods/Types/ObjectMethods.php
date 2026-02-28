<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class ObjectMethods extends GeneralType
{
    public function __construct()
    {
    }

    public function hasProperty()
    {
        return new BaseMethods(
            phpCodeForConversion: 'property_exists(@self, @property)',
            typesOfReturningMethodInPhireScript: ['Bool'],
            allowedTypesOfParams: ['String', 'Bool', 'Array', 'Number'],
            params: [
            new BaseParams(name: '@property', type: 'string', required: true)
            ]
        );
    }

    public function clone()
    {
        return new BaseMethods(
            phpCodeForConversion: 'clone @self',
            typesOfReturningMethodInPhireScript: ['Bool'],
            allowedTypesOfParams: [],
            params: []
        );
    }

    public function getProperties()
    {
        return new BaseMethods(
            phpCodeForConversion: 'get_object_vars(@self)',
            typesOfReturningMethodInPhireScript: ['Array'],
            allowedTypesOfParams: [],
            params: []
        );
    }
}
