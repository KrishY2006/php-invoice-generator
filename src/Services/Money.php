<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class Money
{
    private const BASIS_POINT_DENOMINATOR = 10000;
    private const QUANTITY_SCALE = 100;

    public function lineTotal(int $quantityHundredths, int $unitPriceCents): int
    {
        $this->assertNonNegative($quantityHundredths, 'Quantity');
        $this->assertNonNegative($unitPriceCents, 'Unit price');

        $numerator = $this->multiply($quantityHundredths, $unitPriceCents);

        return $this->divideHalfUp($numerator, self::QUANTITY_SCALE);
    }

    public function discountAmount(int $subtotalCents, int $discountBasisPoints): int
    {
        $this->assertNonNegative($subtotalCents, 'Subtotal');
        $this->assertBasisPoints($discountBasisPoints, 'Discount');

        $numerator = $this->multiply($subtotalCents, $discountBasisPoints);

        return $this->divideHalfUp($numerator, self::BASIS_POINT_DENOMINATOR);
    }

    public function discountedSubtotal(int $subtotalCents, int $discountBasisPoints): int
    {
        return $subtotalCents - $this->discountAmount($subtotalCents, $discountBasisPoints);
    }

    public function taxAmount(int $discountedSubtotalCents, int $taxRateBasisPoints): int
    {
        $this->assertNonNegative($discountedSubtotalCents, 'Discounted subtotal');
        $this->assertBasisPoints($taxRateBasisPoints, 'Tax rate');

        $numerator = $this->multiply($discountedSubtotalCents, $taxRateBasisPoints);

        return $this->divideHalfUp($numerator, self::BASIS_POINT_DENOMINATOR);
    }

    public function total(int $discountedSubtotalCents, int $taxCents): int
    {
        $this->assertNonNegative($discountedSubtotalCents, 'Discounted subtotal');
        $this->assertNonNegative($taxCents, 'Tax');

        if ($discountedSubtotalCents > PHP_INT_MAX - $taxCents) {
            throw new InvalidArgumentException('Money calculation exceeds the supported range.');
        }

        return $discountedSubtotalCents + $taxCents;
    }

    private function divideHalfUp(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('The rounding denominator must be positive.');
        }

        $quotient = intdiv($numerator, $denominator);
        $remainder = $numerator % $denominator;

        if ($remainder * 2 >= $denominator) {
            $quotient++;
        }

        return $quotient;
    }

    private function multiply(int $left, int $right): int
    {
        if ($left === 0 || $right === 0) {
            return 0;
        }

        if ($left > intdiv(PHP_INT_MAX, $right)) {
            throw new InvalidArgumentException('Money calculation exceeds the supported range.');
        }

        return $left * $right;
    }

    private function assertNonNegative(int $value, string $name): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException($name . ' cannot be negative.');
        }
    }

    private function assertBasisPoints(int $value, string $name): void
    {
        if ($value < 0 || $value > self::BASIS_POINT_DENOMINATOR) {
            throw new InvalidArgumentException($name . ' must be between 0 and 10000 basis points.');
        }
    }
}
