<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Taux de change d'une devise vers une autre, à une date donnée.
 * La valeur est conservée en chaîne décimale pour ne jamais passer par un float.
 */
final readonly class Rate
{
    public function __construct(
        public string $base,
        public string $target,
        public string $value,
        public \DateTimeImmutable $date,
    ) {
    }

    public static function identity(string $currency, \DateTimeImmutable $date): self
    {
        return new self($currency, $currency, '1', $date);
    }
}
