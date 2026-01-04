<?php
namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Uuid extends SuperType {

    protected static function transform(mixed $value): mixed {
        return is_string($value) ? strtolower(trim($value)) : $value;
    }

    protected static function validate(mixed $preparedValue): bool {
        if (!is_string($preparedValue)) {
            return false;
        }

        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        return preg_match($regex, $preparedValue) === 1;
    }
}
