<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
  private KernelBrowser $client;
  private EntityManagerInterface $manager;
  private EntityRepository $userRepository;

  protected function setUp(): void {
    $this->client    = static::createClient();
    $this->manager   = static::getContainer()->get('doctrine')->getManager();
    $this->userRepository = $this->manager->getRepository(User::class);

    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }
  }

  public function testUserCanLogin(): void {
    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $container = static::getContainer();

    $entityManager = $container->get(EntityManagerInterface::class);
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new \App\Entity\User();
    $user->setEmail('test@exampleTest.com');

    $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $this->client->request('GET', '/login');

    $form = $crawler->selectButton('Sign in')->form([
      'email' => 'test@exampleTest.com',
      'password' => 'password123',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/');
    $this->client->followRedirect();

    $this->assertSelectorTextContains('title', 'M-motors');
  }
  
  public function testUserAlreadyLogin(): void {
    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $container = static::getContainer();

    $entityManager = $container->get(EntityManagerInterface::class);
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new \App\Entity\User();
    $user->setEmail('test@exampleTestAlready.com');

    $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $this->client->request('GET', '/login');

    $form = $crawler->selectButton('Sign in')->form([
      'email' => 'test@exampleTestAlready.com',
      'password' => 'password123',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/');
    $this->client->followRedirect();

    $this->assertSelectorTextContains('title', 'M-motors');

    $crawler = $this->client->request('GET', '/login');
    $this->client->followRedirect();
    $this->assertRouteSame('app_home');
  }

  public function testUserLogout(): void {
    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $container = static::getContainer();

    $entityManager = $container->get(EntityManagerInterface::class);
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new \App\Entity\User();
    $user->setEmail('test@exampleLogoutTrois.com');

    $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $this->client->request('GET', '/login');

    $form = $crawler->selectButton('Sign in')->form([
      'email' => 'test@exampleLogoutTrois.com',
      'password' => 'password123',
    ]);

    $this->client->submit($form);
    $this->client->followRedirect();
    $this->client->request('GET', '/logout');
    $this->client->followRedirect();

    $token = static::getContainer()
    ->get('security.token_storage')
    ->getToken();

    $this->assertNull($token);

    // TEST qui devrait étre utiliser, mais qui ne l'est pas en abvsence de page protéger à laquel accéder.
    // lorsque ce test sera appliquer supprimer celui utilisant la vérification par token
    $this->client->request('GET', '/user');
    $this->assertResponseRedirects('/login');
  }

  public function testInvalidEmailFailsValidation(): void {
    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $validator = Validation::createValidatorBuilder()
      ->enableAttributeMapping()
      ->getValidator();

    $user = new User();
    $user->setEmail('invalid');

    $errors = $validator->validate($user);

    $this->assertGreaterThan(0, count($errors));
  }
}