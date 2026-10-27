<?php

declare(strict_types=1);

namespace App\Tests\Service\Rate;

use App\Service\Rate\CachedRateProvider;
use App\Tests\Double\InMemoryRateProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedRateProviderTest extends TestCase
{
    public function testItCallsTheInnerProviderOnlyOncePerPair(): void
    {
        $inner = new InMemoryRateProvider();
        $provider = new CachedRateProvider($inner, new ArrayAdapter());

        $first = $provider->getRate('EUR', 'USD');
        $second = $provider->getRate('eur', 'usd');
        $provider->getRate('EUR', 'GBP');

        self::assertEquals($first, $second);
        self::assertSame(2, $inner->calls());
    }

    public function testItCachesTheCurrencyList(): void
    {
        $inner = new InMemoryRateProvider();
        $provider = new CachedRateProvider($inner, new ArrayAdapter());

        $provider->getCurrencies();
        $provider->getCurrencies();

        self::assertSame(1, $inner->calls());
    }
}
