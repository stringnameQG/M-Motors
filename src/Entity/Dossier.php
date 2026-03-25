<?php

namespace App\Entity;

use App\Repository\DossierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DossierRepository::class)]
class Dossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Choice(choices: ['vente', 'location'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Assert\Choice(choices: ['en_cours', 'valide', 'rejeté'])]
    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'dossiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicule $vehicule = null;

    #[Assert\Count(max:10, maxMessage:" {{ limit }} documents autorisées.")]
    #[ORM\Column(nullable: false)]
    private array $documents = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function setDocuments(array $documents): self
    {
        $this->documents = $documents;
        return $this;
    }

    public function addDocument(string $lienCloud): self
    {
        if (count($this->documents) >= 10) {
            throw new \LogicException('Limite de 10 documents atteinte.');
        }
        $this->documents[] = $lienCloud;
        return $this;
    }

    public function removeDocument(string $lienCloud): self
    {
        $this->documents = array_filter($this->documents, fn($url) => $url !== $lienCloud);
        return $this;
    }

}
