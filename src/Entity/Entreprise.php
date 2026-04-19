<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire.')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPublicId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL du site web n\'est pas valide.')]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\OneToOne(inversedBy: 'entreprise', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: JobOffre::class)]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getLogoPublicId(): ?string
    {
        return $this->logoPublicId;
    }

    public function setLogoPublicId(?string $logoPublicId): self
    {
        $this->logoPublicId = $logoPublicId;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
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
            $jobOffre->setEntreprise($this);
        }
        return $this;
    }

    public function removeJobOffre(JobOffre $jobOffre): self
    {
        if ($this->jobOffres->removeElement($jobOffre)) {
            // set the owning side to null (unless already changed)
            if ($jobOffre->getEntreprise() === $this) {
                $jobOffre->setEntreprise(null);
            }
        }
        return $this;
    }
}
