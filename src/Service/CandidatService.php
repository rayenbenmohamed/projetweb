<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;

class CandidatService
{
    private EntityManagerInterface $entityManager;
    private CandidatRepository $candidatRepository;

    public function __construct(EntityManagerInterface $entityManager, CandidatRepository $candidatRepository)
    {
        $this->entityManager = $entityManager;
        $this->candidatRepository = $candidatRepository;
    }

    /**
     * @return Candidat[]
     */
    public function afficher(): array
    {
        return $this->candidatRepository->findAll();
    }

    public function findById(int $id): ?Candidat
    {
        return $this->candidatRepository->find($id);
    }
}
