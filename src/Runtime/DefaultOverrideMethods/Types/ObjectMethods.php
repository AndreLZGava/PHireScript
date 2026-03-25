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
            'hasProperty?',
            phpCodeForConversion: '\property_exists(@self, @property)',
            returnOfPhpExecution: ['Bool'],
            subTypes: ['String', 'Bool', 'Array', 'Number'],
            params: [
            new BaseParams(name: '@property', type: 'string', required: true)
            ]
        );
    }

    public function clone()
    {
        return new BaseMethods(
            'clone',
            phpCodeForConversion: 'clone @self',
            returnOfPhpExecution: ['Object'],
            subTypes: [],
            params: []
        );
    }

    public function getProperties()
    {
        return new BaseMethods(
            'getProperties',
            phpCodeForConversion: '\get_object_vars(@self)',
            returnOfPhpExecution: ['Array'],
            subTypes: [],
            params: []
        );
    }
}
