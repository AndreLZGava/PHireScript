<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class GeneralType
{
    public function destroy()
    {
        return new BaseMethods(
            name: 'destroy!',
            phpCodeForConversion: '\unset(@self)',
            returnOfPhpExecution: [],
        );
    }

    public function defined()
    {
        return new BaseMethods(
            name: 'defined?',
            phpCodeForConversion: 'isset(@self)',
            returnOfPhpExecution: ['Bool'],
        );
    }

    public function empty()
    {
        return new BaseMethods(
            name: 'empty?',
            phpCodeForConversion: 'empty(@self)',
            returnOfPhpExecution: ['Bool'],
        );
    }

    public function getClass()
    {
        return new BaseMethods(
            name: 'getClass',
            phpCodeForConversion: 'GeneralFunctions::getClass(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function show()
    {
        return new BaseMethods(
            name: 'show!',
            phpCodeForConversion: 'GeneralFunctions::show(@self)',
            returnOfPhpExecution: [],
        );
    }

    public function display()
    {
        return new BaseMethods(
            name: 'display!',
            phpCodeForConversion: '\print_r(@self)',
            returnOfPhpExecution: [],
        );
    }

    public function is()
    {
        return new BaseMethods(
            name: 'is?',
            phpCodeForConversion: "GeneralFunctions::is(@self, @type)",
            returnOfPhpExecution: ['Bool'],
            subTypes: [],
            params: [
                new BaseParams(name: '@type', type: 'mixed', required: true)
            ],
        );
    }
}
