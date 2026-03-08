<?php

namespace App\Tests\Integration\Service;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserServiceIntegrationTest extends KernelTestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->userService = self::getContainer()->get('test.user_service');
    }

    public function testRegisterPersistsUserInDatabase(): void
    {
        $user = $this->userService->register(
            'integrationEmail@example.com',
            'password123'
        );

        $this->assertNotNull($user->getId());
        $this->assertNotSame('password123', $user->getPassword());
    }
} 