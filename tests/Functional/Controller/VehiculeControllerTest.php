<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class VehiculeControllerTest extends WebTestCase
{
  private KernelBrowser $client;
  private $container;
  private EntityManagerInterface $entityManager;
  private UserPasswordHasherInterface $passwordHasher;
  private EntityManagerInterface $manager;
  private EntityRepository $userRepository;
  private VehiculeRepository $vehiculeRepository;
  private string $path = '/vehicule/';
  private $user;

  protected function setUp(): void {
    $this->client    = static::createClient();
    $this->container = static::getContainer();
    $this->manager   = static::getContainer()->get('doctrine')->getManager();
    $this->entityManager  = $this->container->get(EntityManagerInterface::class);
    $this->passwordHasher = $this->container->get(UserPasswordHasherInterface::class);
    $this->userRepository = $this->manager->getRepository(User::class);
    $this->vehiculeRepository = $this->manager->getRepository(Vehicule::class);

    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    foreach ($this->userRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

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

  public function testIndex(): void {
    $this->client->followRedirects();
    $this->client->request('GET', $this->path);

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vehicule index');
  }

  public function testNewVehiculeWithValidImage(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/vehicule/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Vehicule');

    
    $this->client->submitForm('Save', [
      'vehicule[type]' => 'location',
      'vehicule[vin]' => 'VF1ABC12345678901',
      'vehicule[immatriculation]' => 'AA-123-AA',
      'vehicule[marque]' => 'Toyota',
      'vehicule[modele]' => 'Corolla',
      'vehicule[version]' => '1.6',
      'vehicule[dateMiseEnCirculation]' => '2020-01-01',
      'vehicule[energie]' => 'Essence',
      'vehicule[boiteVitesse]' => 'Manuelle',
      'vehicule[puissanceFiscale]' => 6,
      'vehicule[kilometrage]' => 50000,
      'vehicule[couleur]' => 'Noir',
      'vehicule[nombrePortes]' => 5,
      'vehicule[nombrePlaces]' => 5,
      'vehicule[photosFiles]' => [
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
    $this->assertRouteSame('app_vehicule_index');

    $vehicule = $this->entityManager->getRepository(Vehicule::class)->findOneBy([], ['id' => 'DESC']);
    $this->assertNotEmpty($vehicule->getCollectionPhotoLien());
    $this->assertStringContainsString('cloudinary', $vehicule->getCollectionPhotoLien()[0]);
  }

  public function testNewVehiculeWithUnvalidImage(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/vehicule/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Vehicule');
    
    $this->client->submitForm('Save', [
      'vehicule[type]' => 'location',
      'vehicule[vin]' => 'VF1ABC12345678901',
      'vehicule[immatriculation]' => 'AA-123-AA',
      'vehicule[marque]' => 'Toyota',
      'vehicule[modele]' => 'Corolla',
      'vehicule[version]' => '1.6',
      'vehicule[dateMiseEnCirculation]' => '2020-01-01',
      'vehicule[energie]' => 'Essence',
      'vehicule[boiteVitesse]' => 'Manuelle',
      'vehicule[puissanceFiscale]' => 6,
      'vehicule[kilometrage]' => 50000,
      'vehicule[couleur]' => 'Noir',
      'vehicule[nombrePortes]' => 5,
      'vehicule[nombrePlaces]' => 5,
      'vehicule[photosFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/valid_document.pdf',
          'valid_document.pdf',
          'pdf',
          null,
          true
        )
      ]
    ]);

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_vehicule_new');
    $this->assertSelectorTextContains("#wrong-upload", 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
  }

  public function testNewVehiculeWithNoImage(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/vehicule/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Vehicule');
    
    $this->client->submitForm('Save', [
      'vehicule[type]' => 'location',
      'vehicule[vin]' => 'VF1ABC12345678901',
      'vehicule[immatriculation]' => 'AA-123-AA',
      'vehicule[marque]' => 'Toyota',
      'vehicule[modele]' => 'Corolla',
      'vehicule[version]' => '1.6',
      'vehicule[dateMiseEnCirculation]' => '2020-01-01',
      'vehicule[energie]' => 'Essence',
      'vehicule[boiteVitesse]' => 'Manuelle',
      'vehicule[puissanceFiscale]' => 6,
      'vehicule[kilometrage]' => 50000,
      'vehicule[couleur]' => 'Noir',
      'vehicule[nombrePortes]' => 5,
      'vehicule[nombrePlaces]' => 5
    ]);

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_vehicule_index');

    $vehicule = $this->entityManager->getRepository(Vehicule::class)->findOneBy([], ['id' => 'DESC']);
    $this->assertEmpty($vehicule->getCollectionPhotoLien());
  }
// test abandonné, bug. Impossible de soummetre un formulaire de test avec plusieurs images.
/*
  public function testNewVehiculeWithToManyImage(): void
  {
    $this->client->followRedirects();
    $this->client->request('GET', '/vehicule/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Vehicule');


    $this->client->submitForm('Save', [
      'vehicule[type]' => 'Berline',
      'vehicule[vin]' => 'VF1ABC12345678901',
      'vehicule[immatriculation]' => 'AA-123-AA',
      'vehicule[marque]' => 'Toyota',
      'vehicule[modele]' => 'Corolla',
      'vehicule[version]' => '1.6',
      'vehicule[dateMiseEnCirculation]' => '2020-01-01',
      'vehicule[energie]' => 'Essence',
      'vehicule[boiteVitesse]' => 'Manuelle',
      'vehicule[puissanceFiscale]' => 6,
      'vehicule[kilometrage]' => 50000,
      'vehicule[couleur]' => 'Noir',
      'vehicule[nombrePortes]' => 5,
      'vehicule[nombrePlaces]' => 5,
      'vehicule[photosFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
          'test_image.jpg',
          'image/jpeg',
          null,
          true
        ),
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_image_copy.jpg',
          'test_image_copy.jpg',
          'image/jpeg',
          null,
          true
        )
      ],
    ]);
    

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_vehicule_new');
    $this->assertSelectorTextContains("#wrong-upload", 'Limite de 10 photos atteinte.');
  }
*/

  public function testShow(): void {
    $fixture = new Vehicule();
    $fixture->setType('vente');
    $fixture->setVin('VF1AB123456789012');
    $fixture->setImmatriculation('AB-123-CD');
    $fixture->setMarque('Renault');
    $fixture->setModele('Clio');
    $fixture->setVersion('V TCe 90 Iconic');
    $fixture->setDateMiseEnCirculation(new \DateTime('2023-05-15'));
    $fixture->setEnergie('Essence');
    $fixture->setBoiteVitesse('Manuelle');
    $fixture->setPuissanceFiscale(5);
    $fixture->setKilometrage(12500);
    $fixture->setCouleur('Bleu Métallisé');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Vehicule');
  }

  public function testEdit(): void { 
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setCollectionPhotoLien(["photo1", "photo2"]);
    $fixture->setType('vente');
    $fixture->setVin('VF1BR000000123456');
    $fixture->setImmatriculation('123-AB-45');
    $fixture->setMarque('Renault');
    $fixture->setModele('4L');
    $fixture->setVersion('R1123');
    $fixture->setDateMiseEnCirculation(new \DateTime('1985-03-22'));
    $fixture->setEnergie('Essence');
    $fixture->setBoiteVitesse('Manuelle');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(123000);
    $fixture->setCouleur('Jaune');
    $fixture->setNombrePortes(3);
    $fixture->setNombrePlaces(4);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'vehicule[type]'                  => 'location',
      'vehicule[vin]'                   => 'VF1BR000000123456',
      'vehicule[immatriculation]'       => '123-AB-45',
      'vehicule[marque]'                => 'Renault',
      'vehicule[modele]'                => '20L',
      'vehicule[version]'               => 'R2000',
      'vehicule[dateMiseEnCirculation]' => '2020-01-22',
      'vehicule[energie]'               => 'Electrique',
      'vehicule[boiteVitesse]'          => 'Automatique',
      'vehicule[puissanceFiscale]'      => 6,
      'vehicule[kilometrage]'           => 150000,
      'vehicule[couleur]'               => 'Bleu',
      'vehicule[nombrePortes]'          => 30,
      'vehicule[nombrePlaces]'          => 40,
    ]);
    
    self::assertResponseRedirects('/vehicule');

    $this->manager->clear();
    $fixture = $this->vehiculeRepository->find($fixture->getId());
  
    self::assertSame('location' , $fixture->getType());
    self::assertSame('VF1BR000000123456' , $fixture->getVin());
    self::assertSame('123-AB-45' , $fixture->getImmatriculation());
    self::assertSame('Renault' , $fixture->getMarque());
    self::assertSame('20L' , $fixture->getModele());
    self::assertSame('R2000' , $fixture->getVersion());
    self::assertSame('Electrique' , $fixture->getEnergie());
    self::assertSame('Automatique' , $fixture->getBoiteVitesse());
    self::assertSame(6 , $fixture->getPuissanceFiscale());
    self::assertSame(150000 , $fixture->getKilometrage());
    self::assertSame('Bleu' , $fixture->getCouleur());
    self::assertSame(30 , $fixture->getNombrePortes());
    self::assertSame(40 , $fixture->getNombrePlaces());
  }

  public function testEditWithValidImage(): void { 
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setCollectionPhotoLien(["photo1", "photo2"]);
    $fixture->setType('vente');
    $fixture->setVin('VF1BR000000123456');
    $fixture->setImmatriculation('123-AB-45');
    $fixture->setMarque('Renault');
    $fixture->setModele('4L');
    $fixture->setVersion('R1123');
    $fixture->setDateMiseEnCirculation(new \DateTime('1985-03-22'));
    $fixture->setEnergie('Essence');
    $fixture->setBoiteVitesse('Manuelle');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(123000);
    $fixture->setCouleur('Jaune');
    $fixture->setNombrePortes(3);
    $fixture->setNombrePlaces(4);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'vehicule[type]'                  => 'location',
      'vehicule[vin]'                   => 'VF1BR000000123456',
      'vehicule[immatriculation]'       => '123-AB-45',
      'vehicule[marque]'                => 'Renault',
      'vehicule[modele]'                => '20L',
      'vehicule[version]'               => 'R2000',
      'vehicule[dateMiseEnCirculation]' => '2020-01-22',
      'vehicule[energie]'               => 'Electrique',
      'vehicule[boiteVitesse]'          => 'Automatique',
      'vehicule[puissanceFiscale]'      => 6,
      'vehicule[kilometrage]'           => 150000,
      'vehicule[couleur]'               => 'Bleu',
      'vehicule[nombrePortes]'          => 30,
      'vehicule[nombrePlaces]'          => 40,
      'vehicule[photosFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
          'test_image.jpg',
          'image/jpeg',
          null,
          true
        )
      ]
    ]);
    
    self::assertResponseRedirects('/vehicule');

    $this->manager->clear();
    $fixture = $this->vehiculeRepository->find($fixture->getId());
  
    self::assertSame('location' , $fixture->getType());
    self::assertSame('VF1BR000000123456' , $fixture->getVin());
    self::assertSame('123-AB-45' , $fixture->getImmatriculation());
    self::assertSame('Renault' , $fixture->getMarque());
    self::assertSame('20L' , $fixture->getModele());
    self::assertSame('R2000' , $fixture->getVersion());
    self::assertSame('Electrique' , $fixture->getEnergie());
    self::assertSame('Automatique' , $fixture->getBoiteVitesse());
    self::assertSame(6 , $fixture->getPuissanceFiscale());
    self::assertSame(150000 , $fixture->getKilometrage());
    self::assertSame('Bleu' , $fixture->getCouleur());
    self::assertSame(30 , $fixture->getNombrePortes());
    self::assertSame(40 , $fixture->getNombrePlaces());
    self::assertCount(3 , $fixture->getCollectionPhotoLien());
  }

  public function testEditWithWithUnvalidImage(): void { 
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setCollectionPhotoLien(["photo1"]);
    $fixture->setType('vente');
    $fixture->setVin('VF1BR000000123456');
    $fixture->setImmatriculation('123-AB-45');
    $fixture->setMarque('Renault');
    $fixture->setModele('4L');
    $fixture->setVersion('R1123');
    $fixture->setDateMiseEnCirculation(new \DateTime('1985-03-22'));
    $fixture->setEnergie('Essence');
    $fixture->setBoiteVitesse('Manuelle');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(123000);
    $fixture->setCouleur('Jaune');
    $fixture->setNombrePortes(3);
    $fixture->setNombrePlaces(4);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'vehicule[type]'                  => 'location',
      'vehicule[vin]'                   => 'VF1BR000000123456',
      'vehicule[immatriculation]'       => '123-AB-45',
      'vehicule[marque]'                => 'Renault',
      'vehicule[modele]'                => '20L',
      'vehicule[version]'               => 'R2000',
      'vehicule[dateMiseEnCirculation]' => '2020-01-22',
      'vehicule[energie]'               => 'Electrique',
      'vehicule[boiteVitesse]'          => 'Automatique',
      'vehicule[puissanceFiscale]'      => 6,
      'vehicule[kilometrage]'           => 150000,
      'vehicule[couleur]'               => 'Bleu',
      'vehicule[nombrePortes]'          => 30,
      'vehicule[nombrePlaces]'          => 40,
      'vehicule[photosFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/valid_document.pdf',
          'valid_document.pdf',
          'pdf',
          null,
          true
        )
      ]
    ]);
    
    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_vehicule_edit');
    $this->assertSelectorTextContains("#wrong-upload", 'Seuls les fichiers JPEG, PNG ou WebP sont autorisés.');
  }

  public function testEditWithToManyImage(): void { 
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setCollectionPhotoLien(["photo1", "photo2", "photo3", "photo4", "photo5", "photo6", "photo7", "photo8", "photo9", "photo10"]);
    $fixture->setType('vente');
    $fixture->setVin('VF1BR000000123456');
    $fixture->setImmatriculation('123-AB-45');
    $fixture->setMarque('Renault');
    $fixture->setModele('4L');
    $fixture->setVersion('R1123');
    $fixture->setDateMiseEnCirculation(new \DateTime('1985-03-22'));
    $fixture->setEnergie('Essence');
    $fixture->setBoiteVitesse('Manuelle');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(123000);
    $fixture->setCouleur('Jaune');
    $fixture->setNombrePortes(3);
    $fixture->setNombrePlaces(4);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'vehicule[type]'                  => 'location',
      'vehicule[vin]'                   => 'VF1BR000000123456',
      'vehicule[immatriculation]'       => '123-AB-45',
      'vehicule[marque]'                => 'Renault',
      'vehicule[modele]'                => '20L',
      'vehicule[version]'               => 'R2000',
      'vehicule[dateMiseEnCirculation]' => '2020-01-22',
      'vehicule[energie]'               => 'Electrique',
      'vehicule[boiteVitesse]'          => 'Automatique',
      'vehicule[puissanceFiscale]'      => 6,
      'vehicule[kilometrage]'           => 150000,
      'vehicule[couleur]'               => 'Bleu',
      'vehicule[nombrePortes]'          => 30,
      'vehicule[nombrePlaces]'          => 40,
      'vehicule[photosFiles]' => [
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
    $this->assertRouteSame('app_vehicule_edit');
    $this->assertSelectorTextContains("#wrong-upload", 'Limite de 10 photos atteinte.');
  }

  public function testRemove(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric Ë-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));
    $this->client->submitForm('Delete');

    self::assertResponseRedirects('/vehicule');
    self::assertSame(0, $this->vehiculeRepository->count([]));
  }

  public function testRemoveWithPhoto(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
    $fixture->setType('vente');
    $fixture->setVin('VF3AE9HZXFM789012');
    $fixture->setImmatriculation('EF-789-GH');
    $fixture->setMarque('Citroën');
    $fixture->setModele('Berlingo');
    $fixture->setVersion('Electric Ë-L4 100kW');
    $fixture->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $fixture->setEnergie('Électrique');
    $fixture->setBoiteVitesse('Automatique');
    $fixture->setPuissanceFiscale(4);
    $fixture->setKilometrage(8000);
    $fixture->setCouleur('Blanc');
    $fixture->setNombrePortes(5);
    $fixture->setNombrePlaces(5);
    $fixture->setCollectionPhotoLien(["test1", "test2"]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));
    $this->client->submitForm('Delete');

    self::assertResponseRedirects('/vehicule');
    self::assertSame(0, $this->vehiculeRepository->count([]));
  }

  public function testRemovePhoto(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
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

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->assertRouteSame('app_vehicule_edit');

    $this->client->submitForm('Supprimer Photo 1');

    $this->client->followRedirects();

    $this->assertRouteSame('app_vehicule_delete_photo');

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));
    
    $this->assertSelectorTextContains("#good-upload", 'Photo supprimée avec succès.');
  }

  public function testRemovePhotoWrongCSRFToken(): void {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
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

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('POST', sprintf('%s%d/delete-photo/%d', $this->path, $fixture->getId(), 0));

    self::assertResponseRedirects('/vehicule');
  }
// Test abandonné, impossible de manipuler la chemin vert un faux index d'image et d'avoir un csrf token valide
/*
  public function testRemovePhotoWrongPhotoIndex(): void
  {
    foreach ($this->vehiculeRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Vehicule();
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

    $this->manager->persist($fixture);
    $this->manager->flush();

    // 1. Faire une requête GET vers la page edit
    $crawler = $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    // Vérifier que la requête a réussi
    $this->assertTrue($this->client->getResponse()->isSuccessful(), 'La requête GET vers la page edit a échoué.');

    // 2. Vérifier que le formulaire est bien trouvé
    $formCount = $crawler->filter('form[action^="/vehicule/' . $fixture->getId() . '/delete-photo/"]')->count();
    dump($formCount); // Cela devrait afficher 1 si le formulaire est correctement trouvé

    // 3. Localiser le formulaire de suppression de photo
    $formNode = $crawler->filter('form[action^="/vehicule/' . $fixture->getId() . '/delete-photo/0"]');

    // 4. Récupérer l'URL actuelle de l'action du formulaire
    $currentActionUrl = $formNode->attr('action');

    // 5. Modifier l'URL pour inclure un index invalide
    $newActionUrl = preg_replace('/\/delete-photo\/\d+$/', '/delete-photo/999', $currentActionUrl);

    // 6. Mettre à jour l'attribut action du formulaire
    $formNode->attr('action', $newActionUrl);

    // 7. Récupérer le formulaire modifié
    $form = $crawler->selectButton('Supprimer Photo 1')->form();

    // 8. Soumettre le formulaire
    $this->client->submit($form);

    $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
    
    $this->assertSelectorTextContains("#wrong-upload", 'Photo non trouvée.');
    
    $this->assertSelectorTextContains("#good-upload", 'Photo supprimée avec succès.');
  }
*/
}