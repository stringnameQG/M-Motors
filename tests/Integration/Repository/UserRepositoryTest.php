<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

class UserRepositoryTest extends KernelTestCase
{
  private EntityManagerInterface $entityManager;
  private UserRepository $userRepository;
  
  protected function setUp(): void
  {
    self::bootKernel();
    $kernel              = self::bootKernel();
    $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    $this->entityManager->createQuery('DELETE FROM App\Entity\User o')->execute();
    $this->userRepository = $this->entityManager->getRepository(User::class);
  }

  public function testUserIsPersistedInDatabase(): void
  {
    $user = new User();
    $user->setEmail('integration@example.com');
    $user->setPassword('hashed');

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    $this->assertNotNull($user->getId());
  }

  public function testUpgradePasswordUpdatesPasswordForValidUser()
  {
    $user = new User();
    $user->setEmail('integrationForThisTest@example.com');
    $newHashedPassword = 'new_hashed_password';

    $this->userRepository->upgradePassword($user, $newHashedPassword);

    $this->assertEquals($newHashedPassword, $user->getPassword());
  }

  public function testUpgradePasswordDoesNotPersistIfUserIsManaged()
  {
    $user = new User();
    $user->setEmail('integrationThisTest@example.com');
    $user->setPassword('TestPassword');
    $newHashedPassword = 'new_hashed_password';

    $this->userRepository->save($user);

    $this->userRepository->upgradePassword($user, $newHashedPassword);

    $this->assertEquals($newHashedPassword, $user->getPassword());
  }

}
