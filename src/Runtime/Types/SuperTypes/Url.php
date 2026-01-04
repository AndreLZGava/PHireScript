<?php

namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Url extends SuperType {
    protected static function validate(mixed $value): bool {
        $result = filter_var($value, FILTER_VALIDATE_URL);

        if ($result === false) {
            return false;
        }

        $parts = parse_url($result);
        if (empty($parts['host'])) {
            return false;
        }

        return true;
    }

    protected static function transform(mixed $value): mixed {
        $value = trim((string)$value);
        return parent::transform($value);
    }
}
