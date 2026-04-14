<?php

namespace App\Service;

use App\Entity\Cv;
use App\Entity\User;
use App\Repository\CvRepository;
use Doctrine\ORM\EntityManagerInterface;

class CvService
{
    private EntityManagerInterface $entityManager;
    private CvRepository $cvRepository;

    public function __construct(EntityManagerInterface $entityManager, CvRepository $cvRepository)
    {
        $this->entityManager = $entityManager;
        $this->cvRepository = $cvRepository;
    }

    public function save(Cv $cv, User $user): void
    {
        $cv->setUser($user);
        $cv->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($cv);
        $this->entityManager->flush();
    }

    public function delete(Cv $cv): void
    {
        $this->entityManager->remove($cv);
        $this->entityManager->flush();
    }

    /**
     * @return Cv[]
     */
    public function getCvByUserId(int $userId): array
    {
        return $this->cvRepository->findBy(['user' => $userId]);
    }

    /**
     * @return Cv[]
     */
    public function afficher(): array
    {
        return $this->cvRepository->findAll();
    }
}
