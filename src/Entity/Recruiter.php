<?php

namespace App\Entity;

use App\Repository\RecruiterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecruiterRepository::class)]
class Recruiter extends User
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $departement = null;

    /** RNE / SIREN (identifiant légal de l’entreprise). */
    #[ORM\Column(name: 'company_rne', length: 32, nullable: true)]
    private ?string $companyRne = null;

    public function getCompanyname(): ?string
    {
        return $this->companyname;
    }

    public function setCompanyname(?string $companyname): self
    {
        $this->companyname = $companyname;
        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): self
    {
        $this->departement = $departement;
        return $this;
    }

    public function getCompanyRne(): ?string
    {
        return $this->companyRne;
    }

    public function setCompanyRne(?string $companyRne): self
    {
        $this->companyRne = $companyRne;

        return $this;
    }
}
