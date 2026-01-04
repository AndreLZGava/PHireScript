<?php

namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Json extends SuperType {

    protected static function transform(mixed $value): mixed {
        if (is_string($value)) {
            $decoded = json_decode($value, true); // true para retornar como array
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
        }

        return $value;
    }

    protected static function validate(mixed $preparedValue): bool {
        return is_array($preparedValue) || is_object($preparedValue);
    }
}
