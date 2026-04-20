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
    #[Assert\NotNull(message: 'La date de début est obligatoire.')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le salaire est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le salaire doit être un nombre positif ou nul.')]
    private ?int $salary = null;

    #[ORM\Column(nullable: true)]
    private ?float $salaireNet = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['En Attente', 'Actif', 'Suspendu', 'Terminé'],
        message: 'Statut invalide. Choisissez parmi : En Attente, Actif, Suspendu, Terminé.'
    )]
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
    #[ORM\JoinColumn(name: 'contract_type_id', nullable: true, onDelete: 'SET NULL')]
    private ?TypeContrat $typeContrat = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'candidate_id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Veuillez sélectionner un candidat.')]
    private ?User $candidate = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'recruiter_id', nullable: true, onDelete: 'SET NULL')]
    private ?User $recruiter = null;

    #[ORM\ManyToOne(targetEntity: JobOffre::class)]
    #[ORM\JoinColumn(name: 'job_offer_id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Veuillez sélectionner une offre d'emploi.")]
    private ?JobOffre $jobOffre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: PdfTemplate::class)]
    #[ORM\JoinColumn(name: 'pdf_template_id', nullable: true, onDelete: 'SET NULL')]
    private ?PdfTemplate $pdfTemplate = null;

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
        // Approximation réaliste tunisienne (CNSS + IRPP) basée sur les données utilisateur
        $this->salaireNet = $salary * 0.82;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getPdfTemplate(): ?PdfTemplate
    {
        return $this->pdfTemplate;
    }

    public function setPdfTemplate(?PdfTemplate $pdfTemplate): self
    {
        $this->pdfTemplate = $pdfTemplate;
        return $this;
    }
}
