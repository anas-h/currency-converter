<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\ConversionResult;
use App\Repository\ConversionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trace persistée d'une conversion effectuée par un utilisateur.
 * Les montants sont stockés en NUMERIC pour éviter toute perte de précision.
 */
#[ORM\Entity(repositoryClass: ConversionRepository::class)]
#[ORM\Index(name: 'IDX_CONVERSION_USER_CREATED', columns: ['user_id', 'created_at'])]
class Conversion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 3)]
    private string $sourceCurrency;

    #[ORM\Column(length: 3)]
    private string $targetCurrency;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 6)]
    private string $sourceAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 6)]
    private string $targetAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 10)]
    private string $rate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $rateDate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, ConversionResult $result, \DateTimeImmutable $createdAt)
    {
        $this->user = $user;
        $this->sourceCurrency = $result->source->getCurrency()->getCurrencyCode();
        $this->targetCurrency = $result->converted->getCurrency()->getCurrencyCode();
        $this->sourceAmount = (string) $result->source->getAmount();
        $this->targetAmount = (string) $result->converted->getAmount();
        $this->rate = $result->rate->value;
        $this->rateDate = $result->rate->date;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSourceCurrency(): string
    {
        return $this->sourceCurrency;
    }

    public function getTargetCurrency(): string
    {
        return $this->targetCurrency;
    }

    public function getSourceAmount(): string
    {
        return $this->sourceAmount;
    }

    public function getTargetAmount(): string
    {
        return $this->targetAmount;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function getRateDate(): \DateTimeImmutable
    {
        return $this->rateDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
