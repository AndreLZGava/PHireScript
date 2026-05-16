<?php

declare(strict_types=1);

namespace PHireScript\Tests\Runtime\Types\MetaTypes;

use PHPUnit\Framework\TestCase;
use PHireScript\Runtime\Types\MetaTypes\Card;

class CardTest extends TestCase
{
    private static function futureExpiry(): string
    {
        $month = (int)date('n') + 1;
        $year  = (int)(date('Y') % 100);

        if ($month > 12) {
            $month = 1;
            $year++;
        }

        return str_pad((string)$month, 2, '0', STR_PAD_LEFT)
             . '/'
             . str_pad((string)$year, 2, '0', STR_PAD_LEFT);
    }

    public function testConstructWithValidData(): void
    {
        $expiry = self::futureExpiry();
        $card = new Card('4111111111111111', '123', 'john doe', $expiry);

        $this->assertIsString($card->number);
        $this->assertSame('Visa', $card->brand);
        $this->assertSame('JOHN DOE', $card->holderName);
        $this->assertIsString($card->expiry);
    }

    public function testDetectsBrandVisa(): void
    {
        $card = new Card('4111111111111111', '123', 'Test User', self::futureExpiry());
        $this->assertSame('Visa', $card->brand);
    }

    public function testDetectsBrandMastercard(): void
    {
        $card = new Card('5500005555555559', '123', 'Test User', self::futureExpiry());
        $this->assertSame('Mastercard', $card->brand);
    }

    public function testDetectsBrandAmex(): void
    {
        $card = new Card('371449635398431', '1234', 'Test User', self::futureExpiry());
        $this->assertSame('Amex', $card->brand);
    }

    public function testDetectsBrandUnknown(): void
    {
        // 6011111111111117 is a Discover card — not in the map
        $card = new Card('6011111111111117', '123', 'Test User', self::futureExpiry());
        $this->assertSame('Unknown', $card->brand);
    }

    public function testHolderNameIsUppercased(): void
    {
        $card = new Card('4111111111111111', '123', 'jane smith', self::futureExpiry());
        $this->assertSame('JANE SMITH', $card->holderName);
    }

    public function testToString(): void
    {
        $expiry = self::futureExpiry();
        $card = new Card('4111111111111111', '123', 'Test User', $expiry);

        $str = (string)$card;

        $this->assertStringStartsWith('Visa ****', $str);
        $this->assertStringContainsString('1111', $str);
        $this->assertStringContainsString('Exp:', $str);
    }

    public function testConstructWithDataArray(): void
    {
        $expiry = self::futureExpiry();
        $card = new Card(data: [
            'number'      => '5500005555555559',
            'cvv'         => '123',
            'holder_name' => 'Maria Silva',
            'expiry'      => $expiry,
        ]);

        $this->assertSame('Mastercard', $card->brand);
        $this->assertSame('MARIA SILVA', $card->holderName);
    }

    public function testInvalidCardNumberThrows(): void
    {
        $this->expectException(\TypeError::class);

        // 4111111111111112 fails Luhn
        new Card('4111111111111112', '123', 'Test User', self::futureExpiry());
    }
}
