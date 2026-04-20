<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
#[ORM\DiscriminatorMap(["user" => "User", "admin" => "Admin", "candidat" => "Candidat", "recruiter" => "Recruiter"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 80)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: "firstName", length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: "lastName", length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twoFactorCode = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $twoFactorEnabled = false;

    #[ORM\Column(name: 'profile_photo_url', length: 512, nullable: true)]
    private ?string $profilePhotoUrl = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeInterface $twoFactorExpiry = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiry = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Entreprise::class, cascade: ['persist', 'remove'])]
    private ?Entreprise $entreprise = null;

    /** Compte bloqué par un admin : connexion refusée jusqu’au déverrouillage. */
    #[ORM\Column(options: ['default' => false])]
    private bool $blocked = false;

    /**
     * Compte recruteur validé par un admin (inscription publique = false jusqu’à approbation).
     * Candidats et admins : true par défaut.
     */
    #[ORM\Column(options: ['default' => true])]
    private bool $approved = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $stored = (string) ($this->role ?? '');
        $primary = match (true) {
            $stored !== '' && str_starts_with($stored, 'ROLE_') => $stored,
            $stored === 'Candidat' => 'ROLE_CANDIDAT',
            $stored === 'Recruteur' => 'ROLE_RECRUTEUR',
            $stored === 'Admin', strcasecmp($stored, 'admin') === 0 => 'ROLE_ADMIN',
            default => 'ROLE_USER',
        };

        return array_values(array_unique(array_merge([$primary, 'ROLE_USER'])));
    }

    public function setRoles(array $roles): self
    {
        $this->role = $roles[0] ?? 'ROLE_USER';
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getTwoFactorCode(): ?string
    {
        return $this->twoFactorCode;
    }

    public function setTwoFactorCode(?string $twoFactorCode): self
    {
        $this->twoFactorCode = $twoFactorCode;
        return $this;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }

    public function setTwoFactorEnabled(bool $twoFactorEnabled): self
    {
        $this->twoFactorEnabled = $twoFactorEnabled;

        return $this;
    }

    public function getProfilePhotoUrl(): ?string
    {
        return $this->profilePhotoUrl;
    }

    public function setProfilePhotoUrl(?string $profilePhotoUrl): self
    {
        $this->profilePhotoUrl = $profilePhotoUrl;

        return $this;
    }

    public function getTwoFactorExpiry(): ?\DateTimeInterface
    {
        return $this->twoFactorExpiry;
    }

    public function setTwoFactorExpiry(?\DateTimeInterface $twoFactorExpiry): self
    {
        $this->twoFactorExpiry = $twoFactorExpiry;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpiry(): ?\DateTimeInterface
    {
        return $this->resetTokenExpiry;
    }

    public function setResetTokenExpiry(?\DateTimeInterface $resetTokenExpiry): self
    {
        $this->resetTokenExpiry = $resetTokenExpiry;
        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(Entreprise $entreprise): self
    {
        // set the owning side of the relation if necessary
        if ($entreprise->getUser() !== $this) {
            $entreprise->setUser($this);
        }

        $this->entreprise = $entreprise;

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }
}
