<?php

declare(strict_types=1);

namespace App\Service\Rate;

use App\Exception\RateProviderUnavailableException;
use App\Exception\UnknownCurrencyException;
use App\Model\Rate;

interface ExchangeRateProviderInterface
{
    /**
     * Devises prises en charge, indexées par code ISO 4217 (ex. ['EUR' => 'Euro']).
     *
     * @return array<string, string>
     *
     * @throws RateProviderUnavailableException
     */
    public function getCurrencies(): array;

    /**
     * @throws UnknownCurrencyException
     * @throws RateProviderUnavailableException
     */
    public function getRate(string $base, string $target): Rate;
}
