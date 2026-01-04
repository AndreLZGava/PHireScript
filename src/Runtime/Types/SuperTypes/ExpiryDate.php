<?php

namespace PHPScript\Runtime\Types\SuperType;

use PHPScript\Runtime\Types\SuperType;

class ExpiryDate extends SuperType {

  protected static function transform(mixed $value): mixed {
    if (!is_string($value)) return $value;

    $clean = preg_replace('/[^0-9]/', '', $value);

    if (strlen($clean) === 6) {
      $clean = substr($clean, 0, 2) . substr($clean, 4, 2);
    }

    return $clean;
  }

  protected static function validate(mixed $preparedValue): bool {
    if (!is_string($preparedValue) || strlen($preparedValue) !== 4) {
      return false;
    }

    $month = (int)substr($preparedValue, 0, 2);
    $year = (int)substr($preparedValue, 2, 2);

    if ($month < 1 || $month > 12) return false;

    $currentYear = (int)date('y');
    $currentMonth = (int)date('n');

    if ($year < $currentYear) return false;
    if ($year === $currentYear && $month < $currentMonth) return false;

    return true;
  }

  public static function isPast(string $value): bool {
    $month = (int)substr($value, 0, 2);
    $year = (int)substr($value, 2, 2);

    $currentYear = (int)date('y');
    $currentMonth = (int)date('n');

    if ($year < $currentYear) return true;
    if ($year === $currentYear && $month < $currentMonth) return true;

    return false;
  }

  public static function format(string $value, string $style = 'short'): string {
    $m = substr($value, 0, 2);
    $y = substr($value, 2, 2);

    return match ($style) {
      'short' => "$m/$y",
      'full'  => "$m/20$y",
      'long'  => $m . " de 20$y",
      'iso'   => "20$y-$m-01",

      default => "$m/$y",
    };
  }
}
