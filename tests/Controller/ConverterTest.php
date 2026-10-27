<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\ConversionRepository;
use App\Tests\Support\CreatesUsers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ConverterTest extends WebTestCase
{
    use CreatesUsers;

    public function testAConversionIsComputedAndRecorded(): void
    {
        $client = self::createClient();
        $client->loginUser(self::createUser());

        $client->request('GET', '/');
        $client->submitForm('Convertir', [
            'conversion[amount]' => '100,00',
            'conversion[from]' => 'EUR',
            'conversion[to]' => 'USD',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.result .big', '= USD 108.50');

        // On vide l'identity map pour relire les valeurs telles que PostgreSQL les a stockées
        self::getContainer()->get(EntityManagerInterface::class)->clear();
        $conversions = self::getContainer()->get(ConversionRepository::class)->findAll();
        self::assertCount(1, $conversions);
        self::assertSame('100.000000', $conversions[0]->getSourceAmount());
        self::assertSame('108.500000', $conversions[0]->getTargetAmount());

        $client->request('GET', '/history');
        self::assertSelectorTextContains('table tbody tr:first-child', '108,50 USD');
    }

    public function testAnInvalidAmountIsRejected(): void
    {
        $client = self::createClient();
        $client->loginUser(self::createUser());

        $client->request('GET', '/');
        $client->submitForm('Convertir', [
            'conversion[amount]' => '-12',
            'conversion[from]' => 'EUR',
            'conversion[to]' => 'USD',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorNotExists('.result');
        self::assertCount(0, self::getContainer()->get(ConversionRepository::class)->findAll());
    }

    public function testUsersOnlySeeTheirOwnHistory(): void
    {
        $client = self::createClient();
        $alice = self::createUser('alice@example.com');
        $bob = self::createUser('bob@example.com');

        $client->loginUser($alice);
        $client->request('GET', '/');
        $client->submitForm('Convertir', ['conversion[amount]' => '1', 'conversion[from]' => 'EUR', 'conversion[to]' => 'USD']);

        $client->loginUser($bob);
        $client->request('GET', '/history');

        self::assertSelectorTextContains('main', 'Aucune conversion');
    }
}
