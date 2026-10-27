<?php

declare(strict_types=1);

namespace App\Service\Rate;

use App\Model\Rate;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Décorateur de cache : Frankfurter publie une fois par jour ouvré,
 * inutile de l'appeler à chaque conversion.
 */
#[AsDecorator(decorates: ExchangeRateProviderInterface::class)]
final class CachedRateProvider implements ExchangeRateProviderInterface
{
    private const RATE_TTL = 3600;
    private const CURRENCIES_TTL = 86400;

    public function __construct(
        #[AutowireDecorated]
        private readonly ExchangeRateProviderInterface $inner,
        private readonly CacheInterface $ratesCache,
    ) {
    }

    public function getCurrencies(): array
    {
        return $this->ratesCache->get('currencies', function (ItemInterface $item): array {
            $item->expiresAfter(self::CURRENCIES_TTL);

            return $this->inner->getCurrencies();
        });
    }

    public function getRate(string $base, string $target): Rate
    {
        $base = strtoupper($base);
        $target = strtoupper($target);

        return $this->ratesCache->get(sprintf('rate.%s.%s', $base, $target), function (ItemInterface $item) use ($base, $target): Rate {
            $item->expiresAfter(self::RATE_TTL);

            return $this->inner->getRate($base, $target);
        });
    }
}
