<?php
namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Slug extends SuperType {

    protected static function transform(mixed $value): mixed {
        if (!is_string($value)) return $value;

        $slug = mb_strtolower($value, 'UTF-8');

        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        $slug = preg_replace('/[\s-]+/', '-', $slug);

        return trim($slug, '-');
    }

    protected static function validate(mixed $preparedValue): bool {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $preparedValue) === 1;
    }
}
