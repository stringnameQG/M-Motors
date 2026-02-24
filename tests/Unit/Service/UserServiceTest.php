<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
  public function testRegisterHashesPasswordAndPersists(): void
  {
    $repository = $this->createMock(UserRepository::class);
    $hasher     = $this->createMock(UserPasswordHasherInterface::class);

    $hasher
      ->expects($this->once())
      ->method('hashPassword')
      ->willReturn('hashed_password');

    $repository
      ->expects($this->once())
      ->method('save')
      ->with($this->isInstanceOf(User::class), true);

    $service = new UserService($repository, $hasher);

    $user = $service->register(
      'test@example.com',
      'plain_password'
    );

    $this->assertSame('test@example.com', $user->getEmail());
    $this->assertSame('hashed_password', $user->getPassword());
    $this->assertContains('ROLE_USER', $user->getRoles());
  }
}