<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Message Entity
 * Represents a chat message between two users in the PetMatch application.
 * 
 * Implementation by: Emna Moussa
 */
#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sentMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receiver = null;

    #[ORM\ManyToOne(targetEntity: PetMatch::class)]
    #[ORM\JoinColumn(name: 'pet_match_id', nullable: true, onDelete: 'CASCADE')]
    private ?PetMatch $petMatch = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $readAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getPetMatch(): ?PetMatch
    {
        return $this->petMatch;
    }

    public function setPetMatch(?PetMatch $petMatch): static
    {
        $this->petMatch = $petMatch;
        return $this;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getReadAt(): ?\DateTime
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTime $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }
}
