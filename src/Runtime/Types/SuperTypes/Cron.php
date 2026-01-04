<?php

namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Cron extends SuperType {

    protected static function transform(mixed $value): mixed {
        if (!is_string($value)) return $value;
        return preg_replace('/\s+/', ' ', trim($value));
    }

    protected static function validate(mixed $preparedValue): bool {
        if (!is_string($preparedValue)) return false;

        $parts = explode(' ', $preparedValue);
        if (count($parts) !== 5) return false;

        $cronRegex = '/^(\*|[0-5]?\d)([\/,-][0-5]?\d)*$/';

        return
            self::checkField($parts[0], 0, 59) &&
            self::checkField($parts[1], 0, 23) &&
            self::checkField($parts[2], 1, 31) &&
            self::checkField($parts[3], 1, 12) &&
            self::checkField($parts[4], 0, 6);
    }

    private static function checkField(string $field, int $min, int $max): bool {
        if ($field === '*') return true;

        $pattern = "/^(\*|\d+)(-\d+)?(\/\d+)?(,\d+(-\d+)?(\/\d+)?)*$/";
        if (!preg_match($pattern, $field)) return false;

        return true;
    }
}
