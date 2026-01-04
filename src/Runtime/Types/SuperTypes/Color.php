<?php
namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class Color extends SuperType {

    protected static function transform(mixed $value): mixed {
        if (!is_string($value)) return $value;

        $color = ltrim(trim($value), '#');

        $color = strtoupper($color);

        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        return "#" . $color;
    }

    protected static function validate(mixed $preparedValue): bool {
        $hex = ltrim($preparedValue, '#');

        return preg_match('/^[0-9A-F]{6}$/', $hex) === 1;
    }
}
