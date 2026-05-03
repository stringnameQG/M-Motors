<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Vehicule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\VehiculeRepository;

class VehiculeRepositoryTest extends KernelTestCase
{
  private EntityManagerInterface $entityManager;
  private VehiculeRepository $vehiculeRepository; 
  
  protected function setUp(): void
  {
    self::bootKernel();
    $kernel              = self::bootKernel();
    $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    $this->entityManager->createQuery('DELETE FROM App\Entity\Vehicule o')->execute();
    $this->vehiculeRepository = $this->entityManager->getRepository(Vehicule::class);
  }

  public function testVehiculeIsPersistedInDatabase(): void
  {
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
<<<<<<< HEAD
    $vehicule->setPrix("5000");
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a

    $this->entityManager->persist($vehicule);
    $this->entityManager->flush();

    $this->assertNotNull($vehicule->getId());
  }
}