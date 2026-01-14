<?php

declare(strict_types=1);

namespace PHPScript\Runtime\Types\SuperTypes;

use PHPScript\Runtime\Types\SuperTypes;

class Mac extends SuperTypes
{
    protected static function validate(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }
}
