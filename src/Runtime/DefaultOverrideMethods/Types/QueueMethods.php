<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class QueueMethods extends GeneralType
{
    public function __construct(
        public array $types,
    ) {
    }

    public function enqueue()
    {
        return new BaseMethods(
            phpCodeForConversion: 'array_push(@self, @params)',
            typesOfReturningMethodInPhireScript: ['self'],
            allowedTypesOfParams: $this->types,
            params: [
                new BaseParams('@params', 'array', true),
            ],
        );
    }

    public function dequeue()
    {
        return new BaseMethods(
            phpCodeForConversion: 'array_shift(@self)',
            typesOfReturningMethodInPhireScript: ['self'],
        );
    }

    public function peek()
    {
        return new BaseMethods(
            phpCodeForConversion: 'reset(@self)',
            typesOfReturningMethodInPhireScript: ['self'],
        );
    }

    public function contains()
    {
        return new BaseMethods(
            phpCodeForConversion: 'in_array(@searching, @self)',
            typesOfReturningMethodInPhireScript: ['Bool'],
            allowedTypesOfParams: $this->types,
            params: [
                new BaseParams(name: '@searching', type: 'mixed', required: true)
            ]
        );
    }
}
