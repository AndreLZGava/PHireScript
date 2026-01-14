<?php

declare(strict_types=1);

namespace PHPScript\Runtime\Types\MetaTypes;

use PHPScript\Runtime\Types\MetaTypes;

class Phone extends MetaTypes
{
    protected static function transform(mixed $value): mixed
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    protected static function validate(mixed $value): bool
    {
        return strlen((string) $value) >= 8;
    }

    public function __toString(): string
    {
        return "+" . $this->innerValue;
    }

    public function getCountryCode(): string
    {
        return substr((string) $this->innerValue, 0, 2);
    }
}
