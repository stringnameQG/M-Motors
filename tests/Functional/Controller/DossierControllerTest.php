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

final class DossierControllerTest extends WebTestCase {
  private KernelBrowser $client;
  private $container;
  private EntityManagerInterface $entityManager;
  private UserPasswordHasherInterface $passwordHasher;
  private EntityManagerInterface $manager;
  private EntityRepository $userRepository;
  private DossierRepository $dossierRepository;
  private VehiculeRepository $vehiculeRepository;
  private string $path = '/dossier/';
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

  public function testIndex(): void {
    $this->client->followRedirects();
    $this->client->request('GET', $this->path);

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Dossier index');
  }

  public function testNewDossierWithValidDocument(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/dossier/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Dossier');
    
    $this->client->submitForm('Save', [
      'dossier[vehicule]' => $this->vehicule->getId(),
      'dossier[documentFiles]' => [
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
    $this->assertRouteSame('app_dossier_index');

    $dossier = $this->entityManager->getRepository(Dossier::class)->findOneBy([], ['id' => 'DESC']);
    $this->assertNotEmpty($dossier->getDocuments());
    $this->assertStringContainsString('cloudinary', $dossier->getDocuments()[0]);
  }

  public function testNewDossierWithUnvalidDocument(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/dossier/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Dossier');
    
    $this->client->submitForm('Save', [
      'dossier[vehicule]' => $this->vehicule->getId(),
      'dossier[documentFiles]' => [
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
    $this->assertRouteSame('app_dossier_new');
  }

  public function testNewDossierWithNoDocument(): void {
    $this->client->followRedirects();
    $this->client->request('GET', '/dossier/new');

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('New Dossier');
    
    $this->client->submitForm('Save', [
      'dossier[vehicule]' => $this->vehicule->getId()
    ]);

    $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    $this->assertRouteSame('app_dossier_index');

    $dossier = $this->entityManager->getRepository(Dossier::class)->findOneBy([], ['id' => 'DESC']);
    $this->assertEmpty($dossier->getDocuments());
  }

  public function testShow(): void {
    $fixture = new Dossier();
    $fixture->setType('vente');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments([]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

    self::assertResponseStatusCodeSame(200);
    self::assertPageTitleContains('Dossier');
  }

  public function testEdit(): void { 
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('location');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments([]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'dossier[vehicule]' => $this->vehicule->getId()
    ]);
    
    self::assertResponseRedirects('/dossier');

    $this->manager->clear();
    $fixture = $this->dossierRepository->find($fixture->getId());
  
    self::assertSame('location' , $fixture->getType());
  }

  public function testEditWithValidDocument(): void { 
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('location');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments([]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'dossier[vehicule]' => $this->vehicule->getId(),
      'dossier[documentFiles]' => [
        new UploadedFile(
          __DIR__.'/../../Unit/fixtures/files/test_image.jpg',
          'test_image.jpg',
          'image/jpeg',
          null,
          true
        )
      ]
    ]);
    
    self::assertResponseRedirects('/dossier');

    $this->manager->clear();
    $fixture = $this->dossierRepository->find($fixture->getId());
  
    self::assertSame('location' , $fixture->getType());
    self::assertCount(1 , $fixture->getDocuments());
  }

  public function testEditWithUnvalidDocument(): void { 
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('location');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments([]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'dossier[vehicule]' => $this->vehicule->getId(),
      'dossier[documentFiles]' => [
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
    $this->assertRouteSame('app_dossier_edit');
    $this->assertSelectorTextContains("#wrong-upload", 'Seuls les fichiers JPEG, PNG WebP ou PDF sont autorisés.');
  }

  public function testEditWithToManyDocument(): void { 
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('location');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments(["doc1", "doc2", "doc3", "doc4", "doc5", "doc6", "doc7", "doc8", "doc9", "doc10"]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->client->submitForm('Update', [
      'dossier[vehicule]' => $this->vehicule->getId(),
      'dossier[documentFiles]' => [
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
    $this->assertRouteSame('app_dossier_edit');
    $this->assertSelectorTextContains("#wrong-upload", 'Limite de 10 documents atteinte.');
  }

  public function testRemove(): void {
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('vente');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments([]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));
    $this->client->submitForm('Delete');

    self::assertResponseRedirects('/dossier');
    self::assertSame(0, $this->dossierRepository->count([]));
  }

  public function testRemoveWithDocument(): void {
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('vente');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments(["photo1", "photo2"]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d', $this->path, $fixture->getId()));
    $this->client->submitForm('Delete');

    self::assertResponseRedirects('/dossier');
    self::assertSame(0, $this->dossierRepository->count([]));
  }

  public function testRemoveDocument(): void {
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('vente');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments(["photo1", "photo2"]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));

    $this->assertRouteSame('app_dossier_edit');

    $this->client->submitForm('Supprimer Document 1');

    $this->client->followRedirects();

    $this->assertRouteSame('app_dossier_delete_document');

    $this->client->request('GET', sprintf('%s%d/edit', $this->path, $fixture->getId()));
    
    $this->assertSelectorTextContains("#good-upload", 'Document supprimée avec succès.');
  }

  public function testRemoveDocumentWrongCSRFToken(): void {
    foreach ($this->dossierRepository->findAll() as $object) {
      $this->manager->remove($object);
    }

    $fixture = new Dossier();
    $fixture->setType('vente');
    $fixture->setStatut('en_cours');
    $fixture->setUser($this->user);
    $fixture->setVehicule($this->vehicule);
    $fixture->setDocuments(["photo1", "photo2"]);

    $this->manager->persist($fixture);
    $this->manager->flush();

    $this->client->request('POST', sprintf('%s%d/delete-document/%d', $this->path, $fixture->getId(), 0));

    self::assertResponseRedirects('/dossier');
  }
}