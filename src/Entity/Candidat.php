<?php

namespace App\Entity;

use App\Repository\CandidatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatRepository::class)]
class Candidat extends User
{
    public function getPrenom(): ?string
    {
        return $this->getFirstName();
    }

    public function setPrenom(?string $firstName): self
    {
        return $this->setFirstName($firstName);
    }

    public function getNom(): ?string
    {
        return $this->getLastName();
    }

    public function setNom(?string $lastName): self
    {
        return $this->setLastName($lastName);
    }
}
