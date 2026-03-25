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
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CommerceControllerTest extends WebTestCase {
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

    $this->client->loginUser($this->user);
  }

  public function testVente(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/vente/");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vente');
  }

  public function testVenteWithId(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/vente/1");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vente');
  }

  public function testVenteWithIdZero(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/vente/0");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vente');
  }

  public function testVenteWithIdToBig(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/vente/100");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vente');
  }

  public function testLocation(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/location/");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Location');
  }

  public function testLocationWithId(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/location/1");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Location');
  }

  public function testLocationWithIdZero(): void {
    $this->client->followRedirects();
    $this->client->request('GET', "/location/0");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Location');
  }

  public function testLocationWithIdToBig(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $this->client->followRedirects();
    $this->client->request('GET', "/location/100");

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Location');
  }

  public function testCommerceVehicule(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new \App\Entity\Vehicule();
    $fixture->setCollectionPhotoLien(["photo1", "photo2"]);
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric E-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setPrix("5000");

    $this->entityManager->persist($fixture);
    $this->manager->flush();

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/commerce/vehicule/%d', $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    $this->assertSelectorExists('img#vehicule-photo-1[src="photo1"]');
    $this->assertSelectorExists('img#vehicule-photo-2[src="photo2"]');
  }
  
  public function testCommerceVehiculeDossier(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new \App\Entity\Vehicule();
    $fixture->setCollectionPhotoLien(["photo3"]);
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric E-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setPrix("5000");

    $this->entityManager->persist($fixture);
    $this->manager->flush();

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/commerce/vehicule/dossier/%d', $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    $this->assertSelectorTextContains("h1", 'Créer un dossier pour Citroën Berlingo');
  }
  
  public function testCommerceVehiculeDossierSubmitWithNoDocument(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new \App\Entity\Vehicule();
    $fixture->setCollectionPhotoLien(["photo3"]);
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric E-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setPrix("5000");

    $this->entityManager->persist($fixture);
    $this->manager->flush();

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/commerce/vehicule/dossier/%d', $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    $this->assertSelectorTextContains("h1", 'Créer un dossier pour Citroën Berlingo');
    
    $this->client->submitForm('Envoyer');

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_home');
  }
  
  public function testCommerceVehiculeDossierSubmitWithValidDocument(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new \App\Entity\Vehicule();
    $fixture->setCollectionPhotoLien(["photo3"]);
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric E-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setPrix("5000");

    $this->entityManager->persist($fixture);
    $this->manager->flush();

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/commerce/vehicule/dossier/%d', $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    $this->assertSelectorTextContains("h1", 'Créer un dossier pour Citroën Berlingo');
    
    $this->client->submitForm('Envoyer', [
      'commerce_dossier[documentFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
          'test_image.jpg',
          'image/jpeg',
          null,
          true
        )
      ]
    ]);

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_home');
  }

  public function testCommerceVehiculeDossierSubmitWithUnvalidDocument(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new \App\Entity\Vehicule();
    $fixture->setCollectionPhotoLien(["photo3"]);
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric E-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setPrix("5000");

    $this->entityManager->persist($fixture);
    $this->manager->flush();

    $this->client->followRedirects();
    $this->client->request('GET', sprintf('/commerce/vehicule/dossier/%d', $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    $this->assertSelectorTextContains("h1", 'Créer un dossier pour Citroën Berlingo');
    
    $this->client->submitForm('Envoyer', [
      'commerce_dossier[documentFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_json.json',
          'test_json.json',
          'application/json',
          null,
          true
        )
      ]
    ]);

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_commerce_vehicule_dossier');
  }
}