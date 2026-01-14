<?php

declare(strict_types=1);

namespace PHPScript\Runtime\Types\SuperTypes;

use PHPScript\Runtime\Types\SuperTypes;

class Ipv4 extends SuperTypes
{
    protected static function validate(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
}
