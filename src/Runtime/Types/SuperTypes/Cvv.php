<?php

namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Cvv extends SuperType {

    protected static function transform(mixed $value): mixed {
        return is_string($value) ? trim($value) : (string)$value;
    }

    protected static function validate(mixed $preparedValue): bool {
        return preg_match('/^\d{3,4}$/', $preparedValue) === 1;
    }
}
