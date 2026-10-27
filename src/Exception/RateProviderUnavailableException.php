<?php

declare(strict_types=1);

namespace App\Exception;

final class RateProviderUnavailableException extends \RuntimeException
{
    public static function because(\Throwable $previous): self
    {
        return new self('Le service de taux de change est momentanément indisponible.', 0, $previous);
    }
}
