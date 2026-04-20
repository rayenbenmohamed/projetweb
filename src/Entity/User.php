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
#[ORM\DiscriminatorMap(["user" => User::class, "admin" => Admin::class, "candidat" => Candidat::class, "recruiter" => Recruiter::class])]
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

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $twoFactorCode = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $twoFactorExpiry = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $resetTokenExpiry = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $googleAccessToken = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $googleRefreshToken = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $googleTokenExpiresAt = null;

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

    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    public function setGoogleAccessToken(?string $googleAccessToken): self
    {
        $this->googleAccessToken = $googleAccessToken;
        return $this;
    }

    public function getGoogleRefreshToken(): ?string
    {
        return $this->googleRefreshToken;
    }

    public function setGoogleRefreshToken(?string $googleRefreshToken): self
    {
        $this->googleRefreshToken = $googleRefreshToken;
        return $this;
    }

    public function getGoogleTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->googleTokenExpiresAt;
    }

    public function setGoogleTokenExpiresAt(?\DateTimeInterface $googleTokenExpiresAt): self
    {
        $this->googleTokenExpiresAt = $googleTokenExpiresAt;
        return $this;
    }
}
