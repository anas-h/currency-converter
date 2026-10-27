<?php

declare(strict_types=1);

namespace App\Exception;

final class UnknownCurrencyException extends \DomainException
{
    public static function forCode(string $code): self
    {
        return new self(sprintf('La devise "%s" n\'est pas prise en charge.', $code));
    }
}
