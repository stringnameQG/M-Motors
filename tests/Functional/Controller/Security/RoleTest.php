<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RoleTest extends WebTestCase
{
  public function testAdminRouteAccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new \App\Entity\User();
        $user->setEmail('testWithAdminRole@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        // Simulez la connexion
        $client->loginUser($user);

        // Testez l'accès à une route protégée
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
    }

    public function testUserWrongRoleRouteAccessDenied(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new \App\Entity\User();
        $user->setEmail('testWithUserRole@example.com');
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
        $client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserNotAuthentifedRedirectToRouteLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/login');
    }
    
    public function testUserNotAuthent(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new \App\Entity\User();
        $user->setEmail('testWithAdminRoleTestRedirect@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();

        $form = $crawler->selectButton('Sign in')->form([
          'email' => 'testWithAdminRoleTestRedirect@example.com',
          'password' => 'password123',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/admin');
    }

}