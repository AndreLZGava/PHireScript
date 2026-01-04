<?php

namespace PHPScript\Runtime\Types;

abstract class SuperType {
  public static function cast(mixed $value): mixed {
    $preparedValue = static::transform($value);

    if (!static::validate($preparedValue)) {
      $type = (new \ReflectionClass(static::class))->getShortName();
      throw new \TypeError("The value ($value) is not a valid type ($type).");
    }

    return $preparedValue;
  }

  abstract protected static function validate(mixed $preparedValue): bool;

  protected static function transform(mixed $value): mixed {
    return $value;
  }
}
