<?php

declare(strict_types=1);

namespace App\Tests\Service\Rate;

use App\Exception\RateProviderUnavailableException;
use App\Exception\UnknownCurrencyException;
use App\Service\Rate\FrankfurterRateProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FrankfurterRateProviderTest extends TestCase
{
    private const BASE_URI = 'https://api.frankfurter.dev/v1/';

    public function testItFetchesTheLatestRateForAPair(): void
    {
        $requested = null;
        $client = new MockHttpClient(function (string $method, string $url) use (&$requested): ResponseInterface {
            $requested = [$method, $url];

            return new MockResponse(self::fixture('frankfurter_latest_eur_usd.json'));
        }, self::BASE_URI);

        $rate = (new FrankfurterRateProvider($client, new NullLogger()))->getRate('eur', 'usd');

        self::assertSame(['GET', self::BASE_URI.'latest?base=EUR&symbols=USD'], $requested);
        self::assertSame('1.0837', $rate->value);
        self::assertSame('2026-10-24', $rate->date->format('Y-m-d'));
    }

    public function testItListsCurrenciesSortedByCode(): void
    {
        $client = new MockHttpClient(new MockResponse(self::fixture('frankfurter_currencies.json')), self::BASE_URI);

        $currencies = (new FrankfurterRateProvider($client, new NullLogger()))->getCurrencies();

        self::assertSame(['EUR', 'GBP', 'JPY', 'USD'], array_keys($currencies));
    }

    public function testItTranslatesA404IntoAnUnknownCurrency(): void
    {
        $client = new MockHttpClient(new MockResponse('{"message":"not found"}', ['http_code' => 404]), self::BASE_URI);

        $this->expectException(UnknownCurrencyException::class);

        (new FrankfurterRateProvider($client, new NullLogger()))->getRate('EUR', 'XXX');
    }

    public function testItTranslatesATransportErrorIntoAnUnavailableProvider(): void
    {
        $client = new MockHttpClient(static fn () => throw new TransportException('Connection refused'), self::BASE_URI);

        $this->expectException(RateProviderUnavailableException::class);

        (new FrankfurterRateProvider($client, new NullLogger()))->getRate('EUR', 'USD');
    }

    private static function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__.'/../../Fixtures/'.$name);
    }
}
