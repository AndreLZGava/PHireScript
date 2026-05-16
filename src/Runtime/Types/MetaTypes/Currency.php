<?php

declare(strict_types=1);

namespace PHireScript\Runtime\Types\MetaTypes;

use NumberFormatter;
use PHireScript\Runtime\Types\MetaTypes;

class Currency extends MetaTypes
{
    protected int $amount;
    protected string $currency;
    protected string $locale;

    public function __construct(mixed $value, string $currency = 'USD', string $locale = 'en_US')
    {
        $this->locale = ($value instanceof self) ? $value->locale : $locale;
        $this->currency = ($value instanceof self) ? $value->currency : strtoupper($currency);

        if ($value instanceof self) {
            $this->amount = $value->cents;
        } else {
            $this->amount = $this->parseValue($value);
        }

        if (!self::validate($this->amount)) {
            throw new \InvalidArgumentException("Invalid currency value.");
        }
    }

    protected function parseValue(mixed $value): int
    {
        if (\is_string($value)) {
            return self::transform($value);
        }

        return self::transform($value);
    }

    protected static function transform(mixed $value): int
    {
        if (\is_int($value)) {
            return $value;
        }
        if (\is_float($value)) {
            return (int) round($value * 100);
        }

        if (\is_string($value)) {
            $clean = preg_replace('/[^\d.,]/', '', $value);

            if (\str_contains((string) $clean, ',') && \str_contains((string) $clean, '.')) {
                // Both separators present: determine which is the decimal one
                // by which appears last (e.g. 1,250.50 vs 1.250,50)
                if (\strrpos((string) $clean, ',') > \strrpos((string) $clean, '.')) {
                    // BR format: dot=thousands, comma=decimal  (1.250,50)
                    $clean = \str_replace('.', '', $clean);
                    $clean = \str_replace(',', '.', $clean);
                } else {
                    // US format: comma=thousands, dot=decimal  (1,250.50)
                    $clean = \str_replace(',', '', $clean);
                }
            } elseif (\str_contains((string) $clean, ',')) {
                // Only comma: treat as decimal separator (50,25 → 50.25)
                $clean = \str_replace(',', '.', $clean);
            }

            $number = (float) $clean;
            return $number === 0.0 && $clean !== '0' && $clean !== '0.0' && $clean !== '0.00'
                ? 0
                : (int) round($number * 100);
        }

        return 0;
    }

    protected static function validate(mixed $value): bool
    {
        return \is_int($value);
    }

    public function __get(string $name)
    {
        return match ($name) {
            'decimal' => $this->amount / 100,
            'cents'   => $this->amount,
            'currency' => $this->currency,
            'locale'   => $this->locale,
            default => null
        };
    }

    public function convertTo(string $newCurrency, float $exchangeRate): self
    {
        $newCents = (int) round($this->amount * $exchangeRate);
        return new self($newCents, $newCurrency, $this->locale);
    }

    public function add(Currency $other): Currency
    {
        if ($this->currency !== $other->currency) {
            throw new \Exception("Not possible sum different currencies. Convert them first.");
        }
        return new self($this->amount + $other->cents, $this->currency, $this->locale);
    }

    public function format(?string $locale = null): string
    {
        $targetLocale = $locale ?? $this->locale;
        $formatter = new NumberFormatter($targetLocale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->decimal, $this->currency);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
