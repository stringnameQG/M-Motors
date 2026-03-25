<?php

namespace App\EventSubscriber;

use App\Entity\Vehicule;
use App\Entity\Dossier;
use App\Service\CloudinaryService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preRemove, priority: 500, connection: 'default')]
class CloudinaryCleanupSubscriber
{
    private CloudinaryService $cloudinary;
    private string $subVehiculeFolder;
    private string $subDossierFolder;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
        $this->subVehiculeFolder = "/vehicules/images";
        $this->subDossierFolder = "/clients/documents";
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Vehicule) $this->handleVehiculeRemoval($entity);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Dossier) $this->handleDossierRemoval($entity);
    }

    private function handleVehiculeRemoval(Vehicule $vehicule): void
    {
        foreach ($vehicule->getCollectionPhotoLien() as $photoUrl) {
            $publicId = $this->recoverId($photoUrl, $this->subVehiculeFolder);
            $this->cloudinary->destroy($publicId);
        }
    }

    private function handleDossierRemoval(Dossier $dossier): void
    {
        foreach ($dossier->getDocuments() as $documentUrl) {
            $publicId = $this->recoverId($documentUrl, $this->subDossierFolder);
            $this->cloudinary->destroy($publicId);
        }
    }

    private function recoverId(string $url, string $folder): string
    {
        $folderIsolate = strstr($url, $folder);
        $ext = strstr($folderIsolate, ".");
        return str_replace($ext, "", $folderIsolate);
    }
}
