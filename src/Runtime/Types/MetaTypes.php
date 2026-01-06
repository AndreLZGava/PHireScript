<?php

namespace PHPScript\Runtime\Types;

abstract class MetaTypes
{
    protected mixed $innerValue;

    public function __construct(mixed $value)
    {
        $this->innerValue = static::transform($value);

        if (!static::validate($this->innerValue)) {
            throw new \TypeError("Couldn't instantiate MetaType " . static::class);
        }
    }

    abstract protected static function transform(mixed $value): mixed;
    abstract protected static function validate(mixed $value): bool;

    abstract public function __toString(): string;
}
