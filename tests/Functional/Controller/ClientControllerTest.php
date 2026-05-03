<?php

namespace App\Tests\Controller;

use App\Entity\Dossier;
use App\Entity\User;
use App\Entity\Vehicule;
use App\Repository\DossierRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
final class ClientControllerTest extends WebTestCase {
  
  private KernelBrowser $client;
  private $container;
  private EntityManagerInterface $entityManager;
  private UserPasswordHasherInterface $passwordHasher;
  private EntityManagerInterface $manager;
  private EntityRepository $userRepository;
  private DossierRepository $dossierRepository;
  private VehiculeRepository $vehiculeRepository;
  private $user;
  private $vehicule;
  private $dossier;

  protected function setUp(): void {
    $this->client    = static::createClient();
    $this->container = static::getContainer();
    $this->manager   = static::getContainer()->get('doctrine')->getManager();
    $this->entityManager  = $this->container->get(EntityManagerInterface::class);
    $this->passwordHasher = $this->container->get(UserPasswordHasherInterface::class);
    $this->userRepository = $this->manager->getRepository(User::class);
    $this->dossierRepository = $this->manager->getRepository(Dossier::class);
    $this->vehiculeRepository = $this->manager->getRepository(Vehicule::class);

    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $this->vehicule = new \App\Entity\Vehicule();
    $this->vehicule->setCollectionPhotoLien(["photo1", "photo2"]);
    $this->vehicule->setType('vente');
    $this->vehicule->setVin('VF3AE9HZXFM789012');
    $this->vehicule->setImmatriculation('EF-789-GH');
    $this->vehicule->setMarque('Citroën');
    $this->vehicule->setModele('Berlingo');
    $this->vehicule->setVersion('Electric E-L4 100kW');
    $this->vehicule->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $this->vehicule->setEnergie('Électrique');
    $this->vehicule->setBoiteVitesse('Automatique');
    $this->vehicule->setPuissanceFiscale(4);
    $this->vehicule->setKilometrage(8000);
    $this->vehicule->setCouleur('Blanc');
    $this->vehicule->setNombrePortes(5);
    $this->vehicule->setNombrePlaces(5);
    $this->vehicule->setPrix("5000");

    $this->entityManager->persist($this->vehicule);
    $this->manager->flush();

    $this->user = new \App\Entity\User();
    $this->user->setEmail('testUser@example.com');
    $this->user->setRoles(['ROLE_ADMIN']);

    $hashedPassword = $this->passwordHasher->hashPassword($this->user, 'password123');
    $this->user->setPassword($hashedPassword);

    $this->entityManager->persist($this->user);
    $this->entityManager->flush();

    $this->dossier = new \App\Entity\Dossier();
    $this->dossier->setType('location');
    $this->dossier->setStatut('en_cours');
    $this->dossier->setUser($this->user);
    $this->dossier->setVehicule($this->vehicule);
    $this->dossier->setDocuments([]);

    $this->entityManager->persist($this->dossier);
    $this->manager->flush();

    $this->client->loginUser($this->user);
  }


  public function testIndexDossier(): void {
    $this->client->loginUser($this->user);

    $this->client->followRedirects();
    $this->client->request('GET', "/client/dossier");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Mes Dossiers');
  }

  public function testIndexDossierWithNoUserConnect(): void {
    $this->client->followRedirects();

    $this->client->request('GET', '/logout');

    $this->client->request('GET', "/client/dossier");
    self::assertResponseStatusCodeSame(200);

    $this->assertRouteSame('app_login');
  }

  public function testDossier(): void {
    $this->client->loginUser($this->user);

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/client/dossier/%d', $this->dossier->getId()));

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Dossier');
  }

  public function testDossierWithNoUserConnect(): void {
    $this->client->followRedirects();

    $this->client->request('GET', '/logout');

    $this->client->request('GET', sprintf('/client/dossier/%d', $this->dossier->getId()));
    self::assertResponseStatusCodeSame(200);

    $this->assertRouteSame('app_login');
  }
}