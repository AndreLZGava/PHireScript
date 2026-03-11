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
            'enqueue!',
            phpCodeForConversion: 'array_push(@self, @params)',
            returnOfPhpExecution: [],
            subTypes: $this->types,
            params: [
                new BaseParams('@params', 'array', true),
            ],
        );
    }

    public function dequeue()
    {
        return new BaseMethods(
            'dequeue!',
            phpCodeForConversion: 'array_shift(@self)',
            returnOfPhpExecution: [],
        );
    }

    public function peek()
    {
        return new BaseMethods(
            'peek',
            phpCodeForConversion: 'reset(@self)',
            returnOfPhpExecution: ['Mixed'],
        );
    }

    public function contains()
    {
        return new BaseMethods(
            name: 'contains?',
            phpCodeForConversion: 'in_array(@searching, @self)',
            returnOfPhpExecution: ['Bool'],
            subTypes: $this->types,
            params: [
                new BaseParams(name: '@searching', type: 'mixed', required: true)
            ]
        );
    }
}
