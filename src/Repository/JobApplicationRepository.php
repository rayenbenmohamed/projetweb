<?php

namespace App\Repository;

use App\Entity\JobApplication;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobApplication>
 */
class JobApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobApplication::class);
    }

    /**
     * Retourne toutes les candidatures reçues sur les offres créées par ce recruteur.
     */
    public function findByRecruiter(User $recruiter): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter)
            ->orderBy('a.applyDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte toutes les candidatures reçues sur les offres créées par ce recruteur.
     */
    public function countByRecruiter(User $recruiter): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
