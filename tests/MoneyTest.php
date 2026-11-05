<?php

declare(strict_types=1);

namespace Tests;

use App\Services\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testLineTotalUsesIntegerMath(): void
    {
        $money = new Money();

        $this->assertSame(1000, $money->lineTotal(100, 1000));
        $this->assertSame(1999, $money->lineTotal(100, 1999));
        $this->assertSame(2999, $money->lineTotal(150, 1999));
    }

    public function testLineTotalRoundsHalfUp(): void
    {
        $money = new Money();

        $this->assertSame(2, $money->lineTotal(50, 3));
        $this->assertSame(1499, $money->lineTotal(150, 999));
        $this->assertSame(663, $money->lineTotal(333, 199));
        $this->assertSame(1489, $money->lineTotal(149, 999));
    }

    public function testLineTotalHandlesZeroValues(): void
    {
        $money = new Money();

        $this->assertSame(0, $money->lineTotal(0, 9999));
        $this->assertSame(0, $money->lineTotal(9999, 0));
        $this->assertSame(0, $money->lineTotal(0, 0));
    }

    public function testLineTotalHandlesLargeValues(): void
    {
        $money = new Money();

        $this->assertSame(
            123456000000,
            $money->lineTotal(100000000, 123456)
        );
    }

    public function testLineTotalRejectsOverflow(): void
    {
        $money = new Money();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds the supported range');

        $money->lineTotal(PHP_INT_MAX, 2);
    }

    public function testLineTotalRejectsNegativeValues(): void
    {
        $money = new Money();

        $this->expectException(InvalidArgumentException::class);

        $money->lineTotal(-1, 100);
    }

    public function testTaxUsesBasisPoints(): void
    {
        $money = new Money();

        $this->assertSame(825, $money->taxAmount(10000, 825));
        $this->assertSame(1080, $money->taxAmount(12345, 875));
        $this->assertSame(101851850935, $money->taxAmount(1234567890123, 825));
    }

    public function testTaxRoundsHalfUp(): void
    {
        $money = new Money();

        $this->assertSame(2, $money->taxAmount(100, 150));
        $this->assertSame(1, $money->taxAmount(100, 149));
    }

    public function testTaxHandlesZeroValues(): void
    {
        $money = new Money();

        $this->assertSame(0, $money->taxAmount(0, 825));
        $this->assertSame(0, $money->taxAmount(10000, 0));
    }

    public function testDiscountUsesBasisPointsAndRoundsHalfUp(): void
    {
        $money = new Money();

        $this->assertSame(1000, $money->discountAmount(10000, 1000));
        $this->assertSame(100, $money->discountAmount(999, 1000));
        $this->assertSame(2, $money->discountAmount(100, 150));
        $this->assertSame(0, $money->discountAmount(10000, 0));
    }

    public function testDiscountedSubtotalCannotBecomeNegative(): void
    {
        $money = new Money();

        $this->assertSame(9000, $money->discountedSubtotal(10000, 1000));
        $this->assertSame(0, $money->discountedSubtotal(1, 10000));
    }

    public function testTaxIsComputedOnDiscountedSubtotal(): void
    {
        $money = new Money();

        $discountedSubtotal = $money->discountedSubtotal(10000, 1000);

        $this->assertSame(9000, $discountedSubtotal);
        $this->assertSame(743, $money->taxAmount($discountedSubtotal, 825));
    }

    public function testTotalAddsDiscountedSubtotalAndTax(): void
    {
        $money = new Money();

        $this->assertSame(9743, $money->total(9000, 743));
        $this->assertSame(1234567891000, $money->total(1234567890000, 1000));
    }

    public function testDiscountRejectsMoreThanOneHundredPercent(): void
    {
        $money = new Money();

        $this->expectException(InvalidArgumentException::class);

        $money->discountAmount(10000, 10001);
    }

    public function testTaxRejectsNegativeRate(): void
    {
        $money = new Money();

        $this->expectException(InvalidArgumentException::class);

        $money->taxAmount(10000, -1);
    }
}
