<?php

namespace App\Entity;

use App\Repository\AvantageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvantageRepository::class)]
class Avantage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToMany(targetEntity: JobOffre::class, mappedBy: 'linkedAvantages')]
    private Collection $jobOffres;

    public function __construct()
    {
        $this->jobOffres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return Collection<int, JobOffre>
     */
    public function getJobOffres(): Collection
    {
        return $this->jobOffres;
    }

    public function addJobOffre(JobOffre $jobOffre): self
    {
        if (!$this->jobOffres->contains($jobOffre)) {
            $this->jobOffres->add($jobOffre);
            $jobOffre->addLinkedAvantage($this);
        }
        return $this;
    }

    public function removeJobOffre(JobOffre $jobOffre): self
    {
        if ($this->jobOffres->removeElement($jobOffre)) {
            $jobOffre->removeLinkedAvantage($this);
        }
        return $this;
    }
}
