<?php

namespace PHPScript\Runtime\Types\MetaType;

use PHPScript\Runtime\Types\MetaType;

class Money extends MetaType {

  protected int $amount;
  protected string $currency;

  public function __construct(mixed $value, string $currency = 'BRL') {
    $this->currency = strtoupper($currency);
    $this->amount = self::transform($value);

    if (!self::validate($this->amount)) {
      throw new \InvalidArgumentException("Invalid currency value.");
    }
  }

  protected static function transform(mixed $value): int {
    if (is_int($value)) return $value;

    if (is_string($value)) {
      $clean = preg_replace('/[^\d.,]/', '', $value);
      if (str_contains($clean, ',') && str_contains($clean, '.')) {
        $clean = str_replace(',', '', $clean);
      } elseif (str_contains($clean, ',')) {
        $clean = str_replace(',', '.', $clean);
      }
      return (int) (round((float)$clean, 2) * 100);
    }

    if (is_float($value)) {
      return (int) (round($value, 2) * 100);
    }

    return 0;
  }

  protected static function validate(mixed $value): bool {
    return is_int($value);
  }

  public function __get(string $name) {
    return match ($name) {
      'decimal' => $this->amount / 100,
      'cents'   => $this->amount,
      'currency' => $this->currency,
      default => null
    };
  }

  public function add(Money $other): Money {
    if ($this->currency !== $other->currency) {
      throw new \Exception("Not possible sum different currencies");
    }
    return new self($this->amount + $other->cents, $this->currency);
  }

  public function format(string $locale = 'pt_BR'): string {
    $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($this->decimal, $this->currency);
  }

  public function __toString(): string {
    return $this->format();
  }
}
