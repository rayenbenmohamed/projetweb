<?php

namespace App\Entity;

use App\Repository\PdfTemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PdfTemplateRepository::class)]
class PdfTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du modèle est obligatoire.')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $primaryColor = '#000000';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $secondaryColor = '#6c757d';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headerHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

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

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): self
    {
        $this->logoPath = $logoPath;
        return $this;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): self
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): self
    {
        $this->secondaryColor = $secondaryColor;
        return $this;
    }

    public function getHeaderHtml(): ?string
    {
        return $this->headerHtml;
    }

    public function setHeaderHtml(?string $headerHtml): self
    {
        $this->headerHtml = $headerHtml;
        return $this;
    }

    public function getFooterHtml(): ?string
    {
        return $this->footerHtml;
    }

    public function setFooterHtml(?string $footerHtml): self
    {
        $this->footerHtml = $footerHtml;
        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Nouveau Modèle';
    }
}
