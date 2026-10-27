<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée un utilisateur en base pour les tests fonctionnels.
 * À utiliser depuis un WebTestCase, après createClient().
 */
trait CreatesUsers
{
    protected const TEST_PASSWORD = 'Test-password-123';

    protected static function createUser(string $email = 'user@example.com'): User
    {
        $container = static::getContainer();
        $hasher = $container->get(UserPasswordHasherInterface::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User($email);
        $user->setPassword($hasher->hashPassword($user, self::TEST_PASSWORD));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
