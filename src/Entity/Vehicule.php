<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Count(max:10, maxMessage:" {{ limit }} photos autorisées.")]
    #[ORM\Column(nullable: true)]
    private ?array $collectionPhotoLien = [];

    #[Assert\Choice(choices: ['vente', 'location'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $vin = null;

    #[ORM\Column(length: 255)]
    private ?string $immatriculation = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[ORM\Column(length: 255)]
    private ?string $version = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateMiseEnCirculation = null;

    #[ORM\Column(length: 255)]
    private ?string $energie = null;

    #[ORM\Column(length: 255)]
    private ?string $boiteVitesse = null;

    #[ORM\Column]
    private ?int $puissanceFiscale = null;

    #[ORM\Column]
    private ?int $kilometrage = null;

    #[ORM\Column(length: 255)]
    private ?string $couleur = null;

    #[ORM\Column]
    private ?int $nombrePortes = null;

    #[ORM\Column]
    private ?int $nombrePlaces = null;
    
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\Positive(message: 'Le prix ne peut pas être vide ou inférieur à zéro')]
    private ?string $prix = null;

    #[ORM\OneToMany(mappedBy: 'vehicule', targetEntity: Dossier::class, orphanRemoval: true)]
    private Collection $dossiers;

    public function __construct()
    {
        $this->dossiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCollectionPhotoLien(): ?array
    {
        return $this->collectionPhotoLien;
    }

    public function setCollectionPhotoLien(?array $collectionPhotoLien): static
    {
        $this->collectionPhotoLien = $collectionPhotoLien;
        return $this;
    }

    public function addCollectionPhotoLien(string $photoUrl): self
    {
        if (count($this->collectionPhotoLien) >= 10) {
            throw new \LogicException('Limite de 10 photos atteinte.');
        }
        $this->collectionPhotoLien[] = $photoUrl;
        return $this;
    }

    public function removeCollectionPhotoLien(string $photoUrl): self
    {
        $this->collectionPhotoLien = array_filter($this->collectionPhotoLien, fn($url) => $url !== $photoUrl);
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): static
    {
        $this->vin = $vin;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getDateMiseEnCirculation(): ?\DateTime
    {
        return $this->dateMiseEnCirculation;
    }

    public function setDateMiseEnCirculation(\DateTime $dateMiseEnCirculation): static
    {
        $this->dateMiseEnCirculation = $dateMiseEnCirculation;

        return $this;
    }

    public function getEnergie(): ?string
    {
        return $this->energie;
    }

    public function setEnergie(string $energie): static
    {
        $this->energie = $energie;

        return $this;
    }

    public function getBoiteVitesse(): ?string
    {
        return $this->boiteVitesse;
    }

    public function setBoiteVitesse(string $boiteVitesse): static
    {
        $this->boiteVitesse = $boiteVitesse;

        return $this;
    }

    public function getPuissanceFiscale(): ?int
    {
        return $this->puissanceFiscale;
    }

    public function setPuissanceFiscale(int $puissanceFiscale): static
    {
        $this->puissanceFiscale = $puissanceFiscale;

        return $this;
    }

    public function getKilometrage(): ?int
    {
        return $this->kilometrage;
    }

    public function setKilometrage(int $kilometrage): static
    {
        $this->kilometrage = $kilometrage;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getNombrePortes(): ?int
    {
        return $this->nombrePortes;
    }

    public function setNombrePortes(int $nombrePortes): static
    {
        $this->nombrePortes = $nombrePortes;

        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
        
    public function getDossiers(): Collection
    {
        return $this->dossiers;
    }
    
    public function addDossier(Dossier $dossier): static
    {
        if (!$this->dossiers->contains($dossier)) {
            $this->dossiers->add($dossier);
            $dossier->setVehicule($this);
        }
        return $this;
    }
    
    public function removeDossier(Dossier $dossier): static
    {
        if ($this->dossiers->removeElement($dossier)) {
            if ($dossier->getVehicule() === $this) {
                $dossier->setVehicule(null);
            }
        }
        return $this;
    }
}
