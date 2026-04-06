<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de fin doit être après la date de début")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le salaire est obligatoire")]
    #[Assert\Positive(message: "Le salaire doit être positif")]
    private ?int $salary = null;

    #[ORM\Column(nullable: true)]
    private ?float $salaireNet = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ["En Attente", "Valide", "Refuse"], message: "Statut invalide")]
    private ?string $status = 'En Attente';

    #[ORM\Column]
    private ?bool $isSigned = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $signedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $signatureBase64 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleEventIdStart = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleEventIdEnd = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleEventIdTrial = null;

    #[ORM\ManyToOne(targetEntity: TypeContrat::class, inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Assert\NotBlank(message: "Le type de contrat est obligatoire")]
    private ?TypeContrat $typeContrat = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $candidate = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $recruiter = null;

    #[ORM\ManyToOne(targetEntity: JobOffre::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JobOffre $jobOffre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): self
    {
        $this->salary = $salary;
        $this->salaireNet = $salary * 0.77;
        return $this;
    }

    public function getSalaireNet(): ?float
    {
        return $this->salaireNet;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isSigned(): ?bool
    {
        return $this->isSigned;
    }

    public function setIsSigned(bool $isSigned): self
    {
        $this->isSigned = $isSigned;
        return $this;
    }

    public function getSignedAt(): ?\DateTimeInterface
    {
        return $this->signedAt;
    }

    public function setSignedAt(?\DateTimeInterface $signedAt): self
    {
        $this->signedAt = $signedAt;
        return $this;
    }

    public function getSignatureBase64(): ?string
    {
        return $this->signatureBase64;
    }

    public function setSignatureBase64(?string $signatureBase64): self
    {
        $this->signatureBase64 = $signatureBase64;
        return $this;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(?TypeContrat $typeContrat): self
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    public function getCandidate(): ?User
    {
        return $this->candidate;
    }

    public function setCandidate(?User $candidate): self
    {
        $this->candidate = $candidate;
        return $this;
    }

    public function getRecruiter(): ?User
    {
        return $this->recruiter;
    }

    public function setRecruiter(?User $recruiter): self
    {
        $this->recruiter = $recruiter;
        return $this;
    }

    public function getJobOffre(): ?JobOffre
    {
        return $this->jobOffre;
    }

    public function setJobOffre(?JobOffre $jobOffre): self
    {
        $this->jobOffre = $jobOffre;
        return $this;
    }
}
