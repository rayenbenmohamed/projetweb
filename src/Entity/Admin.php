<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin extends User
{
    public function manageUsers(): void
    {
        // Admin logic
    }

    public function viewStatistics(): void
    {
        // Admin logic
    }
}
