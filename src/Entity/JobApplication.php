<?php

namespace App\Entity;

use App\Repository\JobApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobApplicationRepository::class)]
#[ORM\Table(name: 'job_application')]
class JobApplication
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_HR_SCREENING = 'HR_SCREENING';
    public const STATUS_INTERVIEW_SCHEDULED = 'INTERVIEW_SCHEDULED';
    public const STATUS_TECHNICAL_TEST = 'TECHNICAL_TEST';
    public const STATUS_FINAL_REVIEW = 'FINAL_REVIEW';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_REJECTED = 'REJECTED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?User $candidat = null;

    #[ORM\ManyToOne(targetEntity: JobOffre::class, inversedBy: 'jobApplications')]
    #[ORM\JoinColumn(name: 'job_offre_id', nullable: false, onDelete: 'CASCADE')]
    private ?JobOffre $jobOffre = null;

    #[ORM\Column(name: 'application_status', length: 50)]
    private ?string $status = 'PENDING';

    #[ORM\Column(name: 'apply_date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $applyDate = null;

    #[ORM\Column(name: 'cover_letter', type: Types::TEXT, nullable: true)]
    private ?string $coverLetter = null;

    #[ORM\Column(name: 'cv_path', length: 255, nullable: true)]
    private ?string $cvPath = null;

    #[ORM\OneToMany(mappedBy: 'application', targetEntity: Interview::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $interviews;

    #[ORM\Column(nullable: true)]
    private ?int $aiScore = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $aiAnalysis = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $aiAnalyzedAt = null;

    public function __construct()
    {
        $this->applyDate = new \DateTime();
        $this->interviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidat(): ?User
    {
        return $this->candidat;
    }

    public function setCandidat(?User $candidat): self
    {
        $this->candidat = $candidat;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getApplyDate(): ?\DateTimeInterface
    {
        return $this->applyDate;
    }

    public function setApplyDate(\DateTimeInterface $applyDate): self
    {
        $this->applyDate = $applyDate;
        return $this;
    }

    public function getCoverLetter(): ?string
    {
        return $this->coverLetter;
    }

    public function setCoverLetter(?string $coverLetter): self
    {
        $this->coverLetter = $coverLetter;
        return $this;
    }

    public function getCvPath(): ?string
    {
        return $this->cvPath;
    }

    public function setCvPath(?string $cvPath): self
    {
        $this->cvPath = $cvPath;
        return $this;
    }

    /**
     * @return Collection<int, Interview>
     */
    public function getInterviews(): Collection
    {
        return $this->interviews;
    }

    public function getAiScore(): ?int
    {
        return $this->aiScore;
    }

    public function setAiScore(?int $aiScore): self
    {
        $this->aiScore = $aiScore;
        return $this;
    }

    public function getAiAnalysis(): ?string
    {
        return $this->aiAnalysis;
    }

    public function setAiAnalysis(?string $aiAnalysis): self
    {
        $this->aiAnalysis = $aiAnalysis;
        return $this;
    }

    public function getAiAnalyzedAt(): ?\DateTimeInterface
    {
        return $this->aiAnalyzedAt;
    }

    public function setAiAnalyzedAt(?\DateTimeInterface $aiAnalyzedAt): self
    {
        $this->aiAnalyzedAt = $aiAnalyzedAt;
        return $this;
    }
}
