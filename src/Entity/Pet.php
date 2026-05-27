<?php

namespace App\Entity;

use App\Repository\PetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Pet Entity
 * Represents a pet in the PetMatch application.
 * 
 * Implementation by: Emna Moussa
 */
#[ORM\Entity(repositoryClass: PetRepository::class)]
#[ORM\Table(name: 'pet')]
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    private ?string $breed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 10)]
    private ?string $gender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getNom(): string
    {
        return (string) $this->name;
    }

    public function setNom(string $nom): static
    {
        return $this->setName($nom);
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

    public function getEspece(): string
    {
        return (string) $this->type;
    }

    public function setEspece(string $espece): static
    {
        return $this->setType($espece);
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(string $breed): static
    {
        $this->breed = $breed;
        return $this;
    }

    public function getRace(): ?string
    {
        return $this->breed;
    }

    public function setRace(?string $race): static
    {
        return $this->setBreed($race ?: '');
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->gender;
    }

    public function setSexe(?string $sexe): static
    {
        return $this->setGender($sexe ?: '');
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->description;
    }

    public function setBio(?string $bio): static
    {
        return $this->setDescription($bio);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getPhoto(): string
    {
        return $this->image ?: 'profil1.webp';
    }

    public function setPhoto(string $photo): static
    {
        return $this->setImage($photo);
    }

    public function getVille(): ?string
    {
        return $this->owner?->getVille();
    }

    public function setVille(?string $ville): static
    {
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
}
