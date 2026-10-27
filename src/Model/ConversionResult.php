<?php

declare(strict_types=1);

namespace App\Model;

use Brick\Money\Money;

final readonly class ConversionResult
{
    public function __construct(
        public Money $source,
        public Money $converted,
        public Rate $rate,
    ) {
    }
}
