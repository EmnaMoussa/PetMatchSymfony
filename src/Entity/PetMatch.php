<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pet_matches')]
#[ORM\UniqueConstraint(name: 'unique_owner_pet_match', columns: ['owner_pet_id', 'pet_id'])]
class PetMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Pet $ownerPet = null;

    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Pet $pet = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getOwnerPet(): ?Pet { return $this->ownerPet; }
    public function setOwnerPet(?Pet $ownerPet): self { $this->ownerPet = $ownerPet; return $this; }
    public function getPet(): ?Pet { return $this->pet; }
    public function setPet(?Pet $pet): self { $this->pet = $pet; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
