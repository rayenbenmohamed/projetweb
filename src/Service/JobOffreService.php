<?php

namespace App\Service;

use App\Entity\JobOffre;
use App\Entity\User;
use App\Repository\JobOffreRepository;
use Doctrine\ORM\EntityManagerInterface;

class JobOffreService
{
    private EntityManagerInterface $entityManager;
    private JobOffreRepository $jobOffreRepository;

    public function __construct(EntityManagerInterface $entityManager, JobOffreRepository $jobOffreRepository)
    {
        $this->entityManager = $entityManager;
        $this->jobOffreRepository = $jobOffreRepository;
    }

    /**
     * @return JobOffre[]
     */
    public function getOffresByCurrentUser(User $user): array
    {
        return $this->jobOffreRepository->findBy(['user' => $user, 'deletedAt' => null]);
    }

    public function save(JobOffre $jobOffre, User $user = null): void
    {
        if ($user) {
            $jobOffre->setUser($user);
        }

        $jobOffre->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($jobOffre);
        $this->entityManager->flush();
    }

    public function softDelete(JobOffre $jobOffre): void
    {
        $jobOffre->setDeletedAt(new \DateTime());
        $this->entityManager->flush();
    }
}
