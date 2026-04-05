<?php

namespace App\Service;

use App\Entity\Recruiter;
use App\Repository\RecruiterRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecruiterService
{
    private EntityManagerInterface $entityManager;
    private RecruiterRepository $recruiterRepository;

    public function __construct(EntityManagerInterface $entityManager, RecruiterRepository $recruiterRepository)
    {
        $this->entityManager = $entityManager;
        $this->recruiterRepository = $recruiterRepository;
    }

    /**
     * @return Recruiter[]
     */
    public function afficher(): array
    {
        return $this->recruiterRepository->findAll();
    }
}
