<?php

declare(strict_types=1);

namespace PHireScript\Tests\Runtime\Types\MetaTypes;

use PHPUnit\Framework\TestCase;
use PHireScript\Runtime\Types\MetaTypes\DateTime;
use PHireScript\Runtime\Types\MetaTypes\Date;
use PHireScript\Runtime\Types\MetaTypes\Time;

class DateTimeTest extends TestCase
{
    public function testConstructWithIsoString(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');

        $this->assertInstanceOf(Date::class, $dt->date);
        $this->assertInstanceOf(Time::class, $dt->time);
    }

    public function testConstructWithNow(): void
    {
        $dt = new DateTime('now');
        $this->assertInstanceOf(DateTime::class, $dt);
    }

    public function testConstructWithDateTimeInterface(): void
    {
        $native = new \DateTime('2023-03-20 09:00:00');
        $dt = new DateTime($native);

        $this->assertInstanceOf(DateTime::class, $dt);
        $this->assertInstanceOf(Date::class, $dt->date);
        $this->assertInstanceOf(Time::class, $dt->time);
    }

    public function testConstructWithBrFormat(): void
    {
        $dt = new DateTime('15/01/2024 10:30:00');

        $this->assertInstanceOf(DateTime::class, $dt);
        $this->assertInstanceOf(Date::class, $dt->date);
        $this->assertInstanceOf(Time::class, $dt->time);
    }

    public function testInvalidStringThrows(): void
    {
        $this->expectException(\TypeError::class);
        new DateTime('not-a-date');
    }

    public function testFormatIso(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $result = $dt->format('iso');

        $this->assertSame('2024-06-15 14:30:00', $result);
    }

    public function testFormatBr(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $result = $dt->format('br');

        // br format: dd/mm/yyyy HH:MM (time short)
        $this->assertSame('15/06/2024 14:30', $result);
    }

    public function testTimestampGetter(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $this->assertIsInt($dt->timestamp);
    }

    public function testToString(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $str = (string)$dt;

        // __toString returns ISO format Y-m-d H:i:s
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $str);
        $this->assertSame('2024-06-15 14:30:00', $str);
    }

    public function testDateProperty(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $this->assertInstanceOf(Date::class, $dt->date);
    }

    public function testTimeProperty(): void
    {
        $dt = new DateTime('2024-06-15 14:30:00');
        $this->assertInstanceOf(Time::class, $dt->time);
    }
}
