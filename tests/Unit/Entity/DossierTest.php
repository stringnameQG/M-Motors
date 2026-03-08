<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Dossier;
use PHPUnit\Framework\TestCase;
use LogicException;

class DossierTest extends TestCase
{
  public function testCreateVehiculCollectionPhotoLien(): void {
    $vehicule = new Dossier();
    $vehicule->setType('vente');
    $vehicule->setStatut('en_cours');
    $vehicule->setCollectionPhotoLien(["photo1", "photo2"]);

    $this->assertEquals(["photo1", "photo2"], $vehicule->getCollectionPhotoLien());
  }


}