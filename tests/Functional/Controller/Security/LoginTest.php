<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
  public function testUserCanLogin(): void
  {
    $client = static::createClient();
    $container = static::getContainer();

    $entityManager = $container->get(EntityManagerInterface::class);
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new \App\Entity\User();
    $user->setEmail('test@example.com');

    $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $client->request('GET', '/login');

    $form = $crawler->selectButton('Sign in')->form([
      'email' => 'test@example.com',
      'password' => 'password123',
    ]);

    $client->submit($form);

    $this->assertResponseRedirects('/');
    $client->followRedirect();

    $this->assertSelectorTextContains('title', 'M-motors Accueil');
  }

  public function testUserLogout(): void
  {
    $client = static::createClient();
    $container = static::getContainer();

    $entityManager = $container->get(EntityManagerInterface::class);
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new \App\Entity\User();
    $user->setEmail('test@exampleLogout.com');

    $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $client->request('GET', '/login');

    $form = $crawler->selectButton('Sign in')->form([
      'email' => 'test@exampleLogout.com',
      'password' => 'password123',
    ]);

    $client->submit($form);
    $client->followRedirect();
    $client->request('GET', '/logout');
    $client->followRedirect();

    $token = static::getContainer()
    ->get('security.token_storage')
    ->getToken();

    $this->assertNull($token);

    // TEST qui devrait étre utiliser, mais qui ne l'est pas en abvsence de page protéger à laquel accéder.
    // lorsque ce test sera appliquer supprimer celui utilisant la vérification par token
    // $client->request('GET', '/dashboard');
    // $this->assertResponseRedirects('/login');
  }

  public function testInvalidEmailFailsValidation(): void
  {
    $validator = Validation::createValidatorBuilder()
      ->enableAttributeMapping()
      ->getValidator();

    $user = new User();
    $user->setEmail('invalid');

    $errors = $validator->validate($user);

    $this->assertGreaterThan(0, count($errors));
  }
}