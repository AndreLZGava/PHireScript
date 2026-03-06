<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class ListMethods extends GeneralType
{
    public function __construct(
        public array $types,
    ) {
    }

    public function enqueue(...$params)
    {
        return new BaseMethods(
            'enqueue!',
            'array_push(@self, @params)',
            [],
            $this->types,
            $params
        );
    }

    public function dequeue()
    {
        return new BaseMethods(
            'dequeue!',
            'array_shift(@self)',
            [],
        );
    }

    public function peek()
    {
        return new BaseMethods(
            'peek',
            'reset(@self)',
            ['self'],
        );
    }
}
