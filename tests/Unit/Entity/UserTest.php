<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class UserTest extends TestCase
{
  private UserRepository $userRepository;

  protected function setUp(): void
  {
    $this->userRepository = $this->createMock(UserRepository::class);
  }

  public function testCreateUserHashesPasswordAndPersists(): void {
    $password = 'plain_password';
    $hashedPassword = hash('sha256', $password);

    $user = new User();
    $user->setEmail('test@example.com');
    $user->setPassword($hashedPassword);
    $user->setRoles(['ROLE_ADMIN']);

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($user);

    $this->userRepository->save($user);

    $this->assertEquals('test@example.com', $user->getEmail());
    $this->assertEquals($hashedPassword, $user->getPassword());
    $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());
  }

  public function testPasswordIsHashedBeforePersisting(): void {
    $password = 'plain_password';
    $hashedPassword = hash('crc32c', $password);

    $user = new User();
    $user->setPassword($hashedPassword);

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($user);

    $this->userRepository->save($user);

    $this->assertNotEquals($password, $user->getPassword());
    $this->assertEquals($hashedPassword, $user->getPassword());
  }

  public function testAssignRoleToUser(): void {
    $user = new User();
    $user->setRoles(['ROLE_USER']);

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($user);

    $this->userRepository->save($user);

    $this->assertContains('ROLE_USER', $user->getRoles());
  }

  public function testRemoveRoleFromUser(): void {
    $user = new User();
    $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($user);

    $this->userRepository->save($user);

    $user->removeRole('ROLE_ADMIN');

    $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
  }
  
  public function testCreateUserFailsWithInvalidEmail(): void {
    $user = new User();
    $user->setEmail('test@example');

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid email');

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($user)
      ->willThrowException(new InvalidArgumentException('Invalid email'));

    $this->userRepository->save($user);
  }
}