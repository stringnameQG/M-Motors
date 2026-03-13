<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Dossier;
use App\Entity\User;
use App\Entity\Vehicule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DossierRepository;

class DossierRepositoryTest extends KernelTestCase
{
  private EntityManagerInterface $entityManager;
  private DossierRepository $dossierRepository; 
  
  protected function setUp(): void
  {
    self::bootKernel();
    $kernel              = self::bootKernel();
    $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    $this->entityManager->createQuery('DELETE FROM App\Entity\Dossier d')->execute();
    $this->dossierRepository = $this->entityManager->getRepository(Dossier::class);
  }

  public function testDocumentIsPersistedInDatabase(): void
  {
    $user = new User();
    $user->setEmail('other@example.com');
    $user->setPassword('hashed');
    
    $vehicule = new Vehicule();
    $vehicule->setType('vente');
    $vehicule->setVin('VF3AE9HZXFM789012');
    $vehicule->setImmatriculation('EF-789-GH');
    $vehicule->setMarque('Citroën');
    $vehicule->setModele('Berlingo');
    $vehicule->setVersion('Electric Ë-L4 100kW');
    $vehicule->setDateMiseEnCirculation(new \DateTime('2024-01-20'));
    $vehicule->setEnergie('Électrique');
    $vehicule->setBoiteVitesse('Automatique');
    $vehicule->setPuissanceFiscale(4);
    $vehicule->setKilometrage(8000);
    $vehicule->setCouleur('Blanc');
    $vehicule->setNombrePortes(5);
    $vehicule->setNombrePlaces(5);

    $this->entityManager->persist($user);
    $this->entityManager->persist($vehicule);

    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser($user);
    $dossier->setVehicule($vehicule);
    $dossier->setDocuments([]);

    $this->entityManager->persist($dossier);
    $this->entityManager->flush();

    $this->assertNotNull($dossier->getId());
  }
}