<?php

namespace App\Entity;

use App\Repository\CoverLetterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoverLetterRepository::class)]
class CoverLetter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire.')]
    #[Assert\Length(max: 200)]
    private ?string $companyName = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le poste est obligatoire.')]
    #[Assert\Length(max: 200)]
    private ?string $position = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $recipientName = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $recipientTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $companyAddress = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu de la lettre ne peut pas être vide.')]
    #[Assert\Length(min: 20, minMessage: 'La lettre doit contenir au moins 20 caractères.')]
    private ?string $letterContent = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $letterFile = null;

    #[ORM\Column]
    private ?bool $isPublic = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    public function setRecipientName(?string $recipientName): static
    {
        $this->recipientName = $recipientName;
        return $this;
    }

    public function getRecipientTitle(): ?string
    {
        return $this->recipientTitle;
    }

    public function setRecipientTitle(?string $recipientTitle): static
    {
        $this->recipientTitle = $recipientTitle;
        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(?string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;
        return $this;
    }

    public function getLetterContent(): ?string
    {
        return $this->letterContent;
    }

    public function setLetterContent(string $letterContent): static
    {
        $this->letterContent = $letterContent;
        return $this;
    }

    public function getLetterFile(): ?string
    {
        return $this->letterFile;
    }

    public function setLetterFile(?string $letterFile): static
    {
        $this->letterFile = $letterFile;
        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
