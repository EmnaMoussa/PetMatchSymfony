<?php

namespace App\Entity;

use App\Repository\SwipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Swipe Entity
 * Represents a swipe (like/pass) action in the PetMatch application.
 * 
 * Implementation by: Emna Moussa
 */
#[ORM\Entity(repositoryClass: SwipeRepository::class)]
#[ORM\Table(name: 'swipe')]
class Swipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'swipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userFrom = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userTo = null;

    #[ORM\Column]
    private ?bool $isLike = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserFrom(): ?User
    {
        return $this->userFrom;
    }

    public function setUserFrom(?User $userFrom): static
    {
        $this->userFrom = $userFrom;
        return $this;
    }

    public function getUserTo(): ?User
    {
        return $this->userTo;
    }

    public function setUserTo(?User $userTo): static
    {
        $this->userTo = $userTo;
        return $this;
    }

    public function isLike(): ?bool
    {
        return $this->isLike;
    }

    public function setIsLike(bool $isLike): static
    {
        $this->isLike = $isLike;
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
}
