<?php

namespace App\Service;

use App\Entity\Interview;
use App\Entity\User;
use App\Repository\InterviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class InterviewService
{
    private EntityManagerInterface $entityManager;
    private InterviewRepository $interviewRepository;

    public function __construct(EntityManagerInterface $entityManager, InterviewRepository $interviewRepository)
    {
        $this->entityManager = $entityManager;
        $this->interviewRepository = $interviewRepository;
    }

    public function schedule(Interview $interview): void
    {
        $this->entityManager->persist($interview);
        $this->entityManager->flush();
    }

    /**
     * @return Interview[]
     */
    public function getInterviewHistory(User $user): array
    {
        // Find interviews where user is recruiter OR candidate
        return $this->interviewRepository->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->join('a.jobOffre', 'jo')
            ->where('jo.user = :user OR a.candidat = :user')
            ->andWhere('i.completedAt IS NOT NULL OR i.status IN (:pastStatuses)')
            ->setParameter('user', $user)
            ->setParameter('pastStatuses', ['Réalisée', 'Annulée', 'Archivée'])
            ->orderBy('i.completedAt', 'DESC')
            ->addOrderBy('i.scheduledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Interview[]
     */
    public function getAllInterviews(User $user): array
    {
        return $this->interviewRepository->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->join('a.jobOffre', 'jo')
            ->where('jo.user = :user OR a.candidat = :user')
            ->setParameter('user', $user)
            ->orderBy('i.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
