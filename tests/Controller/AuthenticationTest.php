<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\Support\CreatesUsers;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthenticationTest extends WebTestCase
{
    use CreatesUsers;

    public function testAnonymousVisitorsAreRedirectedToTheLoginPage(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testAUserCanRegisterAndIsLoggedInAfterwards(): void
    {
        $client = self::createClient();

        $client->request('GET', '/register');
        $client->submitForm("S'inscrire", [
            'registration[email]' => 'alice@example.com',
            'registration[plainPassword][first]' => 'Correct-horse-battery',
            'registration[plainPassword][second]' => 'Correct-horse-battery',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertSelectorTextContains('nav', 'alice@example.com');

        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'alice@example.com']);
        self::assertNotNull($user);
        self::assertNotSame('Correct-horse-battery', $user->getPassword(), 'Le mot de passe doit être haché');
    }

    public function testAUserCanLogInWithTheForm(): void
    {
        $client = self::createClient();
        self::createUser('carol@example.com');

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email' => 'carol@example.com',
            'password' => self::TEST_PASSWORD,
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Convertir un montant');
    }

    public function testLoginFailsWithAWrongPassword(): void
    {
        $client = self::createClient();
        self::createUser('dave@example.com');

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', ['email' => 'dave@example.com', 'password' => 'wrong']);

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorExists('.flash-error');
    }

    public function testLogoutEndsTheSession(): void
    {
        $client = self::createClient();
        $client->loginUser(self::createUser());

        $client->request('GET', '/logout');
        self::assertResponseRedirects('/login');

        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }
}
