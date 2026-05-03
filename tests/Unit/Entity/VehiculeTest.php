<?php

namespace App\Tests\Unit\Entity;

<<<<<<< HEAD
use App\Entity\Dossier;
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
use App\Entity\Vehicule;
use PHPUnit\Framework\TestCase;
use LogicException;

class VehiculeTest extends TestCase
{
  public function testCreateVehiculCollectionPhotoLien(): void {
    $vehicule = new Vehicule();
    $vehicule->setCollectionPhotoLien(["photo1", "photo2"]);
    $vehicule->setType('vente');
    $vehicule->setVin('VF1AB123456789012');
    $vehicule->setImmatriculation('AB-123-CD');
    $vehicule->setMarque('Renault');
    $vehicule->setModele('Clio');
    $vehicule->setVersion('V TCe 90 Iconic');
    $vehicule->setDateMiseEnCirculation(new \DateTime('2023-05-15'));
    $vehicule->setEnergie('Essence');
    $vehicule->setBoiteVitesse('Manuelle');
    $vehicule->setPuissanceFiscale(5);
    $vehicule->setKilometrage(12500);
    $vehicule->setCouleur('Bleu Métallisé');
    $vehicule->setNombrePortes(5);
    $vehicule->setNombrePlaces(5);
<<<<<<< HEAD
    $vehicule->setPrix("5000");
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a

    $this->assertEquals(["photo1", "photo2"], $vehicule->getCollectionPhotoLien());
  }

  public function testCreateVehiculAddPhotoToCollectionPhotoLien(): void {
    $vehicule = new Vehicule();
    $vehicule->setCollectionPhotoLien(["photo1", "photo2"]);
    $vehicule->setType('vente');
    $vehicule->setVin('VF1AB123456789012');
    $vehicule->setImmatriculation('AB-123-CD');
    $vehicule->setMarque('Renault');
    $vehicule->setModele('Clio');
    $vehicule->setVersion('V TCe 90 Iconic');
    $vehicule->setDateMiseEnCirculation(new \DateTime('2023-05-15'));
    $vehicule->setEnergie('Essence');
    $vehicule->setBoiteVitesse('Manuelle');
    $vehicule->setPuissanceFiscale(5);
    $vehicule->setKilometrage(12500);
    $vehicule->setCouleur('Bleu Métallisé');
    $vehicule->setNombrePortes(5);
    $vehicule->setNombrePlaces(5);
<<<<<<< HEAD
    $vehicule->setPrix("5000");
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
    
    $vehicule->addCollectionPhotoLien("photo3");

    $this->assertEquals(["photo1", "photo2", "photo3"], $vehicule->getCollectionPhotoLien());
    $this->assertEquals('vente', $vehicule->getType());
    $this->assertEquals('VF1AB123456789012', $vehicule->getVin());
    $this->assertEquals('AB-123-CD', $vehicule->getImmatriculation());
    $this->assertEquals('Renault', $vehicule->getMarque());
    $this->assertEquals('Clio', $vehicule->getModele());
    $this->assertEquals('V TCe 90 Iconic', $vehicule->getVersion());
    $this->assertEquals(new \DateTime('2023-05-15'), $vehicule->getDateMiseEnCirculation());
    $this->assertEquals('Essence', $vehicule->getEnergie());
    $this->assertEquals('Manuelle', $vehicule->getBoiteVitesse());
    $this->assertEquals(5, $vehicule->getPuissanceFiscale());
    $this->assertEquals(12500, $vehicule->getKilometrage());
    $this->assertEquals('Bleu Métallisé', $vehicule->getCouleur());
    $this->assertEquals(5, $vehicule->getNombrePortes());
    $this->assertEquals(5, $vehicule->getNombrePlaces());
<<<<<<< HEAD
    $this->assertEquals("5000", $vehicule->getPrix());
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
  }

  public function testCreateVehiculAddPhotoToCollectionPhotoLienToMuch(): void {
    $vehicule = new Vehicule();
    $vehicule->setCollectionPhotoLien(["photo1", "photo2", "photo3", "photo4", "photo5", "photo6", "photo7", "photo8", "photo9", "photo10"]);
    $vehicule->setType('vente');
    $vehicule->setVin('VF1AB123456789012');
    $vehicule->setImmatriculation('AB-123-CD');
    $vehicule->setMarque('Renault');
    $vehicule->setModele('Clio');
    $vehicule->setVersion('V TCe 90 Iconic');
    $vehicule->setDateMiseEnCirculation(new \DateTime('2023-05-15'));
    $vehicule->setEnergie('Essence');
    $vehicule->setBoiteVitesse('Manuelle');
    $vehicule->setPuissanceFiscale(5);
    $vehicule->setKilometrage(12500);
    $vehicule->setCouleur('Bleu Métallisé');
    $vehicule->setNombrePortes(5);
    $vehicule->setNombrePlaces(5);
<<<<<<< HEAD
    $vehicule->setPrix("5000");
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a

    try {
        $vehicule->addCollectionPhotoLien("photo11");
        $this->fail("Une exception aurait dû être levée.");
      } catch (LogicException $e) {
        $this->assertEquals('Limite de 10 photos atteinte.', $e->getMessage());
        $this->assertNotContains("photo11", $vehicule->getCollectionPhotoLien());
      }
  }
  
  public function testCreateVehiculRemoveCollectionPhotoLien(): void {
    $vehicule = new Vehicule();
    $vehicule->setCollectionPhotoLien(["photo1", "photo2"]);
    $vehicule->setType('vente');
    $vehicule->setVin('VF1AB123456789012');
    $vehicule->setImmatriculation('AB-123-CD');
    $vehicule->setMarque('Renault');
    $vehicule->setModele('Clio');
    $vehicule->setVersion('V TCe 90 Iconic');
    $vehicule->setDateMiseEnCirculation(new \DateTime('2023-05-15'));
    $vehicule->setEnergie('Essence');
    $vehicule->setBoiteVitesse('Manuelle');
    $vehicule->setPuissanceFiscale(5);
    $vehicule->setKilometrage(12500);
    $vehicule->setCouleur('Bleu Métallisé');
    $vehicule->setNombrePortes(5);
    $vehicule->setNombrePlaces(5);
<<<<<<< HEAD
    $vehicule->setPrix("5000");
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
    
    $vehicule->removeCollectionPhotoLien("photo2");

    $this->assertEquals(["photo1"], $vehicule->getCollectionPhotoLien());
  }
<<<<<<< HEAD

  public function testAddDossier(): void {

    $vehicule = new Vehicule();
    $dossier = new Dossier();

    $vehicule->addDossier($dossier);
    $this->assertCount(1, $vehicule->getDossiers());
    $this->assertSame($vehicule, $dossier->getVehicule());
  }

  public function testRemoveDossier(): void {

    $vehicule = new Vehicule();
    $dossier = new Dossier();

    $vehicule->addDossier($dossier);
    $this->assertCount(1, $vehicule->getDossiers());
    $this->assertSame($vehicule, $dossier->getVehicule());

    $vehicule->removeDossier($dossier);
    $this->assertCount(0, $vehicule->getDossiers());
  }
=======
>>>>>>> 1fbb4c78672f830759cfa07fa9cdb969b405f70a
}