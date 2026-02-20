<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class ListNode extends GeneralType
{
    public function __construct(
        public array $types,
    ) {
    }

    public function enqueue(...$params)
    {
        return new BaseMethods(
            'array_push(@self, @params)',
            ['self'],
            $this->types,
            $params
        );
    }

    public function dequeue()
    {
        return new BaseMethods(
            'array_shift(@self)',
            ['self'],
        );
    }

    public function peek()
    {
        return new BaseMethods(
            'reset(@self)',
            ['self'],
        );
    }
}
