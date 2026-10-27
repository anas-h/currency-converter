<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Exception\UnknownCurrencyException;
use App\Model\Rate;
use App\Service\Rate\ExchangeRateProviderInterface;

/**
 * Fournisseur de taux déterministe pour les tests : aucun appel réseau.
 */
final class InMemoryRateProvider implements ExchangeRateProviderInterface
{
    public const RATE_DATE = '2026-10-24';

    /** @var array<string, array<string, string>> */
    private const RATES = [
        'EUR' => ['USD' => '1.0850', 'GBP' => '0.8520', 'JPY' => '162.37'],
        'USD' => ['EUR' => '0.9217', 'GBP' => '0.7853', 'JPY' => '149.65'],
        'GBP' => ['EUR' => '1.1737', 'USD' => '1.2734', 'JPY' => '190.58'],
        'JPY' => ['EUR' => '0.006159', 'USD' => '0.006682', 'GBP' => '0.005247'],
    ];

    private const CURRENCIES = [
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'USD' => 'US Dollar',
    ];

    private int $calls = 0;

    public function getCurrencies(): array
    {
        ++$this->calls;

        return self::CURRENCIES;
    }

    public function getRate(string $base, string $target): Rate
    {
        ++$this->calls;
        $base = strtoupper($base);
        $target = strtoupper($target);

        if ($base === $target) {
            return Rate::identity($base, new \DateTimeImmutable(self::RATE_DATE));
        }

        $value = self::RATES[$base][$target] ?? throw UnknownCurrencyException::forCode($target);

        return new Rate($base, $target, $value, new \DateTimeImmutable(self::RATE_DATE));
    }

    public function calls(): int
    {
        return $this->calls;
    }
}
