<?php

declare(strict_types=1);

namespace PHireScript\Tests\Runtime\Types\MetaTypes;

use PHPUnit\Framework\TestCase;
use PHireScript\Runtime\Types\MetaTypes\Time;

class TimeTest extends TestCase
{
    public function testConstructWithTimeStringHHMM(): void
    {
        $time = new Time('14:30');

        $this->assertSame(14, $time->hour);
        $this->assertSame(30, $time->minute);
        $this->assertSame(0, $time->second);
    }

    public function testConstructWithTimeStringHHMMSS(): void
    {
        $time = new Time('08:05:15');

        $this->assertSame(8, $time->hour);
        $this->assertSame(5, $time->minute);
        $this->assertSame(15, $time->second);
    }

    public function testConstructWithSeconds(): void
    {
        // 3661 = 1h 1m 1s
        $time = new Time(3661);

        $this->assertSame(1, $time->hour);
        $this->assertSame(1, $time->minute);
        $this->assertSame(1, $time->second);
    }

    public function testConstructWithNow(): void
    {
        $time = new Time('now');
        $this->assertInstanceOf(Time::class, $time);
    }

    public function testInvalidStringThrows(): void
    {
        $this->expectException(\TypeError::class);
        new Time('not-a-time');
    }

    public function testInvalidHour(): void
    {
        $this->expectException(\TypeError::class);
        // 25:00 — hour 25 is out of range for the regex [0-1]?[0-9]|2[0-3]
        new Time('25:00');
    }

    public function testTotalSecondsGetter(): void
    {
        $time = new Time(3661);
        $this->assertSame(3661, $time->total_seconds);
    }

    public function testFormatShort(): void
    {
        $time = new Time('14:30');
        $this->assertSame('14:30', $time->format('short'));
    }

    public function testFormatFull(): void
    {
        $time = new Time('14:30:00');
        $this->assertSame('14:30:00', $time->format('full'));
    }

    public function testFormat12h(): void
    {
        $time = new Time('14:00');
        $result = $time->format('12h');
        // date("g:i a", ...) for 14:00 → "2:00 pm"
        $this->assertSame('2:00 pm', $result);
    }

    public function testToString(): void
    {
        $time = new Time('09:05:07');
        $this->assertSame('09:05:07', (string)$time);
    }
}
