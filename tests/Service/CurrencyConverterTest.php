<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\UnknownCurrencyException;
use App\Service\CurrencyConverter;
use App\Tests\Double\InMemoryRateProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CurrencyConverterTest extends TestCase
{
    private CurrencyConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new CurrencyConverter(new InMemoryRateProvider());
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function conversions(): iterable
    {
        yield 'EUR vers USD' => ['100', 'EUR', 'USD', 'USD 108.50'];
        yield 'arrondi demi-supérieur' => ['10.005', 'EUR', 'USD', 'USD 10.86'];
        yield 'yen sans décimales' => ['1', 'EUR', 'JPY', 'JPY 162'];
        yield 'même devise' => ['42.42', 'EUR', 'EUR', 'EUR 42.42'];
        yield 'pas de dérive flottante' => ['0.1', 'USD', 'EUR', 'EUR 0.09'];
    }

    #[DataProvider('conversions')]
    public function testItConvertsWithExactDecimalArithmetic(string $amount, string $from, string $to, string $expected): void
    {
        $result = $this->converter->convert($amount, $from, $to);

        self::assertSame($expected, (string) $result->converted);
        self::assertSame(InMemoryRateProvider::RATE_DATE, $result->rate->date->format('Y-m-d'));
    }

    public function testItRejectsAnUnknownCurrency(): void
    {
        $this->expectException(UnknownCurrencyException::class);

        $this->converter->convert('1', 'EUR', 'ZZZ');
    }

    public function testItRejectsANegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->converter->convert('-5', 'EUR', 'USD');
    }
}
