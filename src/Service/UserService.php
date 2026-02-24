<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
  public function __construct(
    private UserRepository $repository,
    private UserPasswordHasherInterface $passwordHasher
  ) {}

  public function register(string $email, string $plainPassword): User
  {
    $user = new User();
    $user->setEmail($email);

    $hashedPassword = $this->passwordHasher->hashPassword(
      $user,
      $plainPassword
    );

    $user->setPassword($hashedPassword);
    $user->setRoles(['ROLE_USER']);

    $this->repository->save($user, true);

    return $user;
  }
}