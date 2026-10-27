<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UnknownCurrencyException;
use App\Model\ConversionResult;
use App\Service\Rate\ExchangeRateProviderInterface;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\UnknownCurrencyException as BrickUnknownCurrency;
use Brick\Money\Money;

/**
 * Cœur métier : convertit un montant d'une devise vers une autre
 * avec une arithmétique décimale exacte (brick/money).
 */
final class CurrencyConverter
{
    public function __construct(
        private readonly ExchangeRateProviderInterface $rateProvider,
    ) {
    }

    /**
     * @param string $amount montant décimal sous forme de chaîne, ex. "100.50"
     *
     * @throws UnknownCurrencyException
     * @throws \InvalidArgumentException si le montant est invalide ou négatif
     */
    public function convert(string $amount, string $from, string $to): ConversionResult
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        try {
            $source = Money::of(trim($amount), $from, roundingMode: RoundingMode::HalfUp);
        } catch (BrickUnknownCurrency) {
            throw UnknownCurrencyException::forCode($from);
        } catch (MathException $e) {
            throw new \InvalidArgumentException(sprintf('Montant invalide : "%s".', $amount), 0, $e);
        }

        if ($source->isNegative()) {
            throw new \InvalidArgumentException('Le montant ne peut pas être négatif.');
        }

        $rate = $this->rateProvider->getRate($from, $to);

        try {
            $converted = $source->convertedTo($to, $rate->value, roundingMode: RoundingMode::HalfUp);
        } catch (BrickUnknownCurrency) {
            throw UnknownCurrencyException::forCode($to);
        }

        return new ConversionResult($source, $converted, $rate);
    }
}
