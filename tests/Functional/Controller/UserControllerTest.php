<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private $container;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $manager;
    private EntityRepository $userRepository;
    private string $path = '/user/';
    private $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->manager->getRepository(User::class);
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->passwordHasher = $this->container->get(UserPasswordHasherInterface::class);

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();

        $this->user = new \App\Entity\User();
        $this->user->setEmail('testCRUDUser@example.com');
        $this->user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($this->user, 'password123');
        $this->user->setPassword($hashedPassword);

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {

        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[email]' => 'testNewUser@example.com',
            'user[password]' => 'Testing78978Y87UI',
        ]);

        self::assertResponseRedirects('/user');

        self::assertSame(2, $this->userRepository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new User();
        $fixture->setEmail('testUserFixture@example.com');
        $fixture->setRoles(['ROLE_ADMIN']);
        $fixture->setPassword('Testing78978Y87UI');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');
    }

    public function testEdit(): void
    {
        $fixture = new User();
        $fixture->setEmail('testUserFixtureOne@example.com');
        $fixture->setRoles(['ROLE_ADMIN']);
        $fixture->setPassword('Testing78978Y87UIF');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'testUserFixtureTwo@example.com',
            'user[password]' => 'Testing78978Y87UIFF',
        ]);

        self::assertResponseRedirects('/user');

        $fixture = $this->userRepository->findAll();

        self::assertSame('testUserFixtureTwo@example.com', $fixture[0]->getEmail());
        self::assertSame(['ROLE_ADMIN'], $fixture[0]->getRoles());
        self::assertSame('Testing78978Y87UIFF', $fixture[0]->getPassword());
    }

    public function testRemove(): void
    {
        $fixture = new User();
        $fixture->setEmail('testUserWillRemove.com');
        $fixture->setRoles(['ROLE_ADMIN']);
        $fixture->setPassword('Testing78978Y87UIFF344');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/user');
        self::assertSame(1, $this->userRepository->count([]));
    }
}