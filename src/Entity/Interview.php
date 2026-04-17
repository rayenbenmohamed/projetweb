<?php

namespace App\Entity;

use App\Repository\InterviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InterviewRepository::class)]
class Interview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de l'entretien est obligatoire.")]
    #[Assert\GreaterThan("now", message: "La date de l'entretien doit être dans le futur.")]
    private ?\DateTimeInterface $scheduledAt = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $status = 'Prévue';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "Les notes sont obligatoires pour justifier l'entretien.")]
    #[Assert\Length(min: 10, minMessage: "Les notes doivent faire au moins 10 caractères.")]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le lieu ou le lien de réunion est obligatoire.")]
    #[Assert\Length(min: 5, minMessage: "Le lieu ou lien doit faire au moins 5 caractères.")]
    private ?string $meetingLink = null;

    #[ORM\ManyToOne(targetEntity: JobApplication::class, inversedBy: 'interviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JobApplication $application = null;

    #[ORM\Column(nullable: true)]
    private ?int $technicalRating = null;

    #[ORM\Column(nullable: true)]
    private ?int $communicationRating = null;

    #[ORM\Column(nullable: true)]
    private ?int $motivationRating = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $finalVerdict = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $outcome = null; // ACCEPTED, REJECTED, PENDING_DECISION

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getMeetingLink(): ?string
    {
        return $this->meetingLink;
    }

    public function setMeetingLink(?string $meetingLink): self
    {
        $this->meetingLink = $meetingLink;
        return $this;
    }

    public function getApplication(): ?JobApplication
    {
        return $this->application;
    }

    public function setApplication(?JobApplication $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getTechnicalRating(): ?int
    {
        return $this->technicalRating;
    }

    public function setTechnicalRating(?int $technicalRating): self
    {
        $this->technicalRating = $technicalRating;
        return $this;
    }

    public function getCommunicationRating(): ?int
    {
        return $this->communicationRating;
    }

    public function setCommunicationRating(?int $communicationRating): self
    {
        $this->communicationRating = $communicationRating;
        return $this;
    }

    public function getMotivationRating(): ?int
    {
        return $this->motivationRating;
    }

    public function setMotivationRating(?int $motivationRating): self
    {
        $this->motivationRating = $motivationRating;
        return $this;
    }

    public function getFinalVerdict(): ?string
    {
        return $this->finalVerdict;
    }

    public function setFinalVerdict(?string $finalVerdict): self
    {
        $this->finalVerdict = $finalVerdict;
        return $this;
    }

    public function getOutcome(): ?string
    {
        return $this->outcome;
    }

    public function setOutcome(?string $outcome): self
    {
        $this->outcome = $outcome;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }
}
