<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Dossier;
use App\Entity\User;
use App\Entity\Vehicule;
use PHPUnit\Framework\TestCase;
use LogicException;

class DossierTest extends TestCase
{
  public function testCreateVehiculCollectionPhotoLien(): void {
    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser(new User());
    $dossier->setVehicule(new Vehicule());
    $dossier->setDocuments([]);
    
    $this->assertEquals('vente', $dossier->getType());
    $this->assertEquals('en_cours', $dossier->getStatut());
    $this->assertEquals(new User(), $dossier->getUser());
    $this->assertEquals(new Vehicule(), $dossier->getVehicule());
    $this->assertEquals([], $dossier->getDocuments());
  }

  public function testAddDocument(): void
  {
    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser(new User());
    $dossier->setVehicule(new Vehicule());
    $dossier->setDocuments([]);
    
    $this->assertEquals('vente', $dossier->getType());
    $this->assertEquals('en_cours', $dossier->getStatut());
    $this->assertEquals(new User(), $dossier->getUser());
    $this->assertEquals(new Vehicule(), $dossier->getVehicule());
    $this->assertEquals([], $dossier->getDocuments());

    $dossier->addDocument('https://cloud.example.com/piece_identite.pdf');

    $this->assertEquals(['https://cloud.example.com/piece_identite.pdf'], $dossier->getDocuments());
  }

  public function testSetDocuments(): void
  {
    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser(new User());
    $dossier->setVehicule(new Vehicule());
    $dossier->setDocuments([]);
    
    $this->assertEquals('vente', $dossier->getType());
    $this->assertEquals('en_cours', $dossier->getStatut());
    $this->assertEquals(new User(), $dossier->getUser());
    $this->assertEquals(new Vehicule(), $dossier->getVehicule());
    $this->assertEquals([], $dossier->getDocuments());

    $dossier->setDocuments(['https://cloud.example.com/piece_identite.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
    ]);

    $this->assertCount(2, $dossier->getDocuments());
  }

  public function testRemoveDocuments(): void
  {
    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser(new User());
    $dossier->setVehicule(new Vehicule());
    $dossier->setDocuments([]);
    
    $this->assertEquals('vente', $dossier->getType());
    $this->assertEquals('en_cours', $dossier->getStatut());
    $this->assertEquals(new User(), $dossier->getUser());
    $this->assertEquals(new Vehicule(), $dossier->getVehicule());
    $this->assertEquals([], $dossier->getDocuments());

    $dossier->setDocuments(['https://cloud.example.com/piece_identite.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
    ]);

    $dossier->removeDocument('https://cloud.example.com/piece_identite.pdf');

    $this->assertCount(1, $dossier->getDocuments());
  }

  public function testDocumentsAddToMuchDocuments(): void
  {
    $dossier = new Dossier();
    $dossier->setType('vente');
    $dossier->setStatut('en_cours');
    $dossier->setUser(new User());
    $dossier->setVehicule(new Vehicule());
    $dossier->setDocuments([]);
    
    $this->assertEquals('vente', $dossier->getType());
    $this->assertEquals('en_cours', $dossier->getStatut());
    $this->assertEquals(new User(), $dossier->getUser());
    $this->assertEquals(new Vehicule(), $dossier->getVehicule());
    $this->assertEquals([], $dossier->getDocuments());

    $dossier->setDocuments([
      'https://cloud.example.com/piece_identite.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
      'https://cloud.example.com/justificatif_domicile.pdf',
    ]);

    try {
        $dossier->addDocument("photo11");
        $this->fail("Une exception aurait dû être levée.");
      } catch (LogicException $e) {
        $this->assertEquals('Limite de 10 documents atteinte.', $e->getMessage());
        $this->assertNotContains("photo11", $dossier->getDocuments());
      }
  }
}