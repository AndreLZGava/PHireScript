<?php
namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Ipv4 extends SuperType {
    protected static function validate(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
}
