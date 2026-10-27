<?php

declare(strict_types=1);

namespace App\Service\Rate;

use App\Exception\RateProviderUnavailableException;
use App\Exception\UnknownCurrencyException;
use App\Model\Rate;
use Brick\Math\BigDecimal;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client de l'API Frankfurter (https://frankfurter.dev) : taux de référence de la BCE.
 */
final class FrankfurterRateProvider implements ExchangeRateProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $frankfurterClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getCurrencies(): array
    {
        $data = $this->request('currencies');

        $currencies = [];
        foreach ($data as $code => $name) {
            if (\is_string($code) && \is_string($name)) {
                $currencies[strtoupper($code)] = $name;
            }
        }
        ksort($currencies);

        return $currencies;
    }

    public function getRate(string $base, string $target): Rate
    {
        $base = strtoupper($base);
        $target = strtoupper($target);

        if ($base === $target) {
            return Rate::identity($base, new \DateTimeImmutable('today'));
        }

        $data = $this->request('latest', ['base' => $base, 'symbols' => $target]);

        $value = $data['rates'][$target] ?? null;
        if (!\is_int($value) && !\is_float($value) && !\is_string($value)) {
            throw UnknownCurrencyException::forCode($target);
        }

        $date = \is_string($data['date'] ?? null) ? $data['date'] : 'today';

        return new Rate(
            base: $base,
            target: $target,
            value: (string) BigDecimal::of((string) $value),
            date: new \DateTimeImmutable($date),
        );
    }

    /**
     * @param array<string, string> $query
     *
     * @return array<mixed>
     */
    private function request(string $path, array $query = []): array
    {
        try {
            $response = $this->frankfurterClient->request('GET', $path, ['query' => $query]);
            $status = $response->getStatusCode();

            // Frankfurter répond 404 (ou 422) quand une devise est inconnue
            if (\in_array($status, [404, 422], true)) {
                throw UnknownCurrencyException::forCode($query['symbols'] ?? $query['base'] ?? $path);
            }

            return $response->toArray();
        } catch (HttpClientException $e) {
            $this->logger->error('Frankfurter API call failed', [
                'path' => $path,
                'query' => $query,
                'exception' => $e,
            ]);

            throw RateProviderUnavailableException::because($e);
        }
    }
}
