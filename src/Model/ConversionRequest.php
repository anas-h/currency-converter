<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO porté par le formulaire de conversion. Le montant reste une chaîne
 * jusqu'au calcul pour éviter les arrondis flottants.
 */
final class ConversionRequest
{
    #[Assert\NotBlank(message: 'Veuillez saisir un montant.')]
    #[Assert\Regex(
        pattern: '/^\d{1,12}([.,]\d{1,6})?$/',
        message: 'Le montant doit être un nombre positif avec au plus 6 décimales.'
    )]
    public ?string $amount = '1';

    #[Assert\NotBlank]
    #[Assert\Currency]
    public ?string $from = 'EUR';

    #[Assert\NotBlank]
    #[Assert\Currency]
    public ?string $to = 'USD';

    public function normalizedAmount(): string
    {
        return str_replace(',', '.', trim((string) $this->amount));
    }
}
