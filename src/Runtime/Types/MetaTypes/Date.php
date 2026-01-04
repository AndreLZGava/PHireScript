<?php

namespace PHPScript\Runtime\Types\MetaType;

use PHPScript\Runtime\Types\MetaType;

class Date extends MetaType {

  protected \DateTimeImmutable $date;

  protected static array $formatAliases = [
    'iso'      => 'Y-m-d',
    'iso_full' => 'Y-m-d H:i:s',
    'br'       => 'd/m/Y',
    'br_time'  => 'd/m/Y H:i',
    'human'    => 'j \d\e F \d\e Y',
    'atom'     => \DateTimeInterface::ATOM,
  ];

  public function __construct(mixed $value = 'now') {
    $transformed = self::transform($value);

    if (!self::validate($transformed)) {
      throw new \TypeError("Date format invalid!");
    }

    $this->date = $transformed->setTime(0, 0, 0);
  }

  protected static function transform(mixed $value): mixed {
    if ($value instanceof \DateTimeInterface) {
      return \DateTimeImmutable::createFromInterface($value);
    }

    try {
      if (is_numeric($value)) {
        return (new \DateTimeImmutable())->setTimestamp((int)$value);
      }

      if (is_string($value)) {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
          return \DateTimeImmutable::createFromFormat('d/m/Y', $value);
        }
        return new \DateTimeImmutable($value);
      }
    } catch (\Exception $e) {
      return false;
    }

    return false;
  }

  protected static function validate(mixed $value): bool {
    return $value instanceof \DateTimeInterface;
  }

  public function __get(string $name) {
    return match ($name) {
      'year'  => (int)$this->date->format('Y'),
      'month' => (int)$this->date->format('m'),
      'day'   => (int)$this->date->format('d'),
      'timestamp' => $this->date->getTimestamp(),
      default => null
    };
  }

  public function toPhpDateTime(): \DateTimeImmutable {
    $datePart = (string) $this->date;
    $timePart = (string) $this->time;

    return new \DateTimeImmutable($datePart . ' ' . $timePart);
  }

  public function format(string $style = 'iso'): string {
    $mask = self::$formatAliases[$style] ?? $style;
    return $this->toPhpDateTime()->format($mask);
  }

  public function __toString(): string {
    return $this->format('iso');
  }
}
