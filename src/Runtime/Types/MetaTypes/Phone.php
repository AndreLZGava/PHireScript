<?php
namespace PHPScript\Runtime\Types\MetaType;

use PHPScript\Runtime\Types\MetaType;

class Phone extends MetaType {
    protected static function transform(mixed $value): mixed {
        return preg_replace('/\D/', '', $value);
    }

    protected static function validate(mixed $value): bool {
        return strlen($value) >= 8;
    }

    public function __toString(): string {
        return "+" . $this->innerValue;
    }

    public function getCountryCode(): string {
        return substr($this->innerValue, 0, 2);
    }
}
