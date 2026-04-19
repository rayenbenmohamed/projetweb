<?php

namespace App\Entity;

use App\Repository\JobOffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobOffreRepository::class)]
#[ORM\Table(name: 'job_offre')]
class JobOffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 5,
        max: 150,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        min: 20,
        max: 5000,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Le lieu ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le salaire doit être égal ou supérieur à 0.')]
    #[Assert\LessThanOrEqual(value: 500000, message: 'Le salaire ne peut pas dépasser 500 000 €.')]
    private ?float $salary = null;

    #[ORM\Column(name: 'publishedAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotNull(message: 'La date de publication est obligatoire.')]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['DRAFT', 'PUBLISHED', 'ARCHIVED'],
        message: 'Le statut doit être "DRAFT", "PUBLISHED" ou "ARCHIVED".'
    )]
    private ?string $status = 'DRAFT';

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(name: 'employment_type', length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le type d\'emploi est obligatoire.')]
    #[Assert\Choice(
        choices: ['CDI', 'CDD', 'Freelance', 'Stage', 'Alternance'],
        message: 'Le type d\'emploi sélectionné n\'est pas valide.'
    )]
    private ?string $employmentType = null;

    #[ORM\Column(name: 'is_salary_negotiable')]
    private ?bool $isSalaryNegotiable = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: 'Les avantages texte ne peuvent pas dépasser {{ limit }} caractères.')]
    private ?string $advantages = null;

    #[ORM\Column(name: 'company_logo', length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $companyLogo = null;

    #[ORM\Column(name: 'company_logo_public_id', length: 255, nullable: true)]
    private ?string $companyLogoPublicId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $skills = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'jobOffres')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Entreprise $entreprise = null;

    #[ORM\OneToMany(mappedBy: 'jobOffre', targetEntity: JobApplication::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $jobApplications;

    #[ORM\ManyToMany(targetEntity: Avantage::class, inversedBy: 'jobOffres')]
    #[ORM\JoinTable(name: 'job_offre_avantage')]
    private Collection $linkedAvantages;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->jobApplications = new ArrayCollection();
        $this->linkedAvantages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function setSalary(?float $salary): self
    {
        $this->salary = $salary;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt < new \DateTime();
    }

    public function getDisplayStatus(): string
    {
        if ($this->status === 'PUBLISHED' && $this->isExpired()) {
            return 'ARCHIVED';
        }
        return $this->status ?? 'DRAFT';
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getEmploymentType(): ?string
    {
        return $this->employmentType;
    }

    public function setEmploymentType(?string $employmentType): self
    {
        $this->employmentType = $employmentType;
        return $this;
    }

    public function isSalaryNegotiable(): ?bool
    {
        return $this->isSalaryNegotiable;
    }

    public function setSalaryNegotiable(bool $isSalaryNegotiable): self
    {
        $this->isSalaryNegotiable = $isSalaryNegotiable;
        return $this;
    }

    public function getAdvantages(): ?string
    {
        return $this->advantages;
    }

    public function setAdvantages(?string $advantages): self
    {
        $this->advantages = $advantages;
        return $this;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
    }

    public function setCompanyLogo(?string $companyLogo): self
    {
        $this->companyLogo = $companyLogo;
        return $this;
    }

    public function getCompanyLogoPublicId(): ?string
    {
        return $this->companyLogoPublicId;
    }

    public function setCompanyLogoPublicId(?string $companyLogoPublicId): self
    {
        $this->companyLogoPublicId = $companyLogoPublicId;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, JobApplication>
     */
    public function getJobApplications(): Collection
    {
        return $this->jobApplications;
    }

    /**
     * @return Collection<int, Avantage>
     */
    public function getLinkedAvantages(): Collection
    {
        return $this->linkedAvantages;
    }

    public function addLinkedAvantage(Avantage $avantage): self
    {
        if (!$this->linkedAvantages->contains($avantage)) {
            $this->linkedAvantages->add($avantage);
        }
        return $this;
    }

    public function removeLinkedAvantage(Avantage $avantage): self
    {
        $this->linkedAvantages->removeElement($avantage);
        return $this;
    }

    public function getSkills(): ?string
    {
        return $this->skills;
    }

    public function setSkills(?string $skills): self
    {
        $this->skills = $skills;
        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;
        return $this;
    }
}
