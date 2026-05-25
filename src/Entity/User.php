<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $prenom = '';

    #[ORM\Column(length: 100)]
    private string $nom = '';

    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    #[ORM\Column(length: 255)]
    private string $passwordHash = '';

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Pet::class, cascade: ['persist', 'remove'])]
    private Collection $pets;

    public function __construct()
    {
        $this->pets = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = strtolower($email); return $this; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $passwordHash): self { $this->passwordHash = $passwordHash; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): self { $this->ville = $ville; return $this; }
    public function getPets(): Collection { return $this->pets; }
    public function getFullName(): string { return trim($this->prenom . ' ' . $this->nom); }
}
