<?php
namespace PHPScript\Runtime\Types\SuperTypes;

use PHPScript\Runtime\Types\SuperTypes;

class Email extends SuperTypes {
    protected static function validate(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
