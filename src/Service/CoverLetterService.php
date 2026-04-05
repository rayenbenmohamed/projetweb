<?php

namespace App\Service;

use App\Entity\CoverLetter;
use App\Entity\User;
use App\Repository\CoverLetterRepository;
use Doctrine\ORM\EntityManagerInterface;

class CoverLetterService
{
    private EntityManagerInterface $entityManager;
    private CoverLetterRepository $coverLetterRepository;

    public function __construct(EntityManagerInterface $entityManager, CoverLetterRepository $coverLetterRepository)
    {
        $this->entityManager = $entityManager;
        $this->coverLetterRepository = $coverLetterRepository;
    }

    public function save(CoverLetter $coverLetter, User $user): void
    {
        $coverLetter->setUser($user);
        $coverLetter->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($coverLetter);
        $this->entityManager->flush();
    }

    public function delete(CoverLetter $coverLetter): void
    {
        $this->entityManager->remove($coverLetter);
        $this->entityManager->flush();
    }

    /**
     * @return CoverLetter[]
     */
    public function getCoverLettersByUserId(int $userId): array
    {
        return $this->coverLetterRepository->findBy(['user' => $userId]);
    }

    /**
     * @return CoverLetter[]
     */
    public function afficher(): array
    {
        return $this->coverLetterRepository->findAll();
    }
}
