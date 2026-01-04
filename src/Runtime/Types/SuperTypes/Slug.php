<?php

namespace PHPScript\Runtime\Types\SuperTypes;

use PHPScript\Runtime\Types\SuperTypes;

class Slug extends SuperTypes {

    protected static function transform(mixed $value): mixed {
        if (!is_string($value)) return null;

        $slug = mb_strtolower($value, 'UTF-8');
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        $slug = preg_replace('/[^a-z0-9]+/', ' ', $slug);

        $slug = preg_replace('/\s+/', '-', $slug);

        return trim($slug, '-');
    }

    protected static function validate(mixed $preparedValue): bool {
        if (is_null($preparedValue) || !is_scalar($preparedValue)) {
            return false;
        }
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $preparedValue) === 1;
    }
}
