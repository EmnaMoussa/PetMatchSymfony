<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pets')]
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\Column(length: 100)]
    private string $nom = '';

    #[ORM\Column(length: 60)]
    private string $espece = '';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $race = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255)]
    private string $photo = 'profil1.webp';

    public function getId(): ?int { return $this->id; }
    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): self { $this->owner = $owner; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function getEspece(): string { return $this->espece; }
    public function setEspece(string $espece): self { $this->espece = $espece; return $this; }
    public function getRace(): ?string { return $this->race; }
    public function setRace(?string $race): self { $this->race = $race; return $this; }
    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): self { $this->age = $age; return $this; }
    public function getSexe(): ?string { return $this->sexe; }
    public function setSexe(?string $sexe): self { $this->sexe = $sexe; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): self { $this->ville = $ville; return $this; }
    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }
    public function getPhoto(): string { return $this->photo; }
    public function setPhoto(string $photo): self { $this->photo = $photo; return $this; }
}
