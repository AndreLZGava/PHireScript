<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class StackMethods extends GeneralType
{
    public function __construct(
        public array $types,
    ) {
    }

    public function push(...$params)
    {
        return new BaseMethods(
            'array_push(@self, @params)',
            ['self'],
            $this->types,
            $params
        );
    }

    public function pop()
    {
        return new BaseMethods(
            'array_pop(@self)',
            ['self'],
        );
    }

    public function peek()
    {
        return new BaseMethods(
            'end(@self)',
            ['self'],
        );
    }
}
