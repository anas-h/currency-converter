<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Conversion;
use App\Entity\User;
use App\Model\ConversionResult;
use App\Repository\ConversionRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ConversionHistory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConversionRepository $conversions,
    ) {
    }

    public function record(User $user, ConversionResult $result): Conversion
    {
        $conversion = new Conversion($user, $result, new \DateTimeImmutable());

        $this->entityManager->persist($conversion);
        $this->entityManager->flush();

        return $conversion;
    }

    /** @return list<Conversion> */
    public function forUser(User $user, int $limit = 20): array
    {
        return $this->conversions->findLatestForUser($user, $limit);
    }
}
