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
     * Retourne les candidatures reçues sur les offres du recruteur, avec filtres et tri IA.
     */
    public function findByRecruiter(User $recruiter, ?string $offerTitle = null, ?string $status = null, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter);

        if ($offerTitle) {
            $qb->andWhere('o.title LIKE :title')
               ->setParameter('title', '%' . $offerTitle . '%');
        }

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        // Tri intelligent : Score IA d'abord (desc), puis date (desc)
        return $qb->orderBy('a.aiScore', 'DESC')
            ->addOrderBy('a.applyDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne UNIQUEMENT les candidatures avec un score IA pour un recruteur.
     * Beaucoup plus léger que de charger tout puis filtrer en PHP.
     *
     * @return array<JobApplication>
     */
    public function findScoredForRecruiter(User $recruiter): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->andWhere('a.aiScore IS NOT NULL')
            ->setParameter('recruiter', $recruiter)
            ->orderBy('a.aiScore', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les candidatures reçues pour un recruteur avec filtres.
     */
    public function countByRecruiter(User $recruiter, ?string $offerTitle = null, ?string $status = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter);

        if ($offerTitle) {
            $qb->andWhere('o.title LIKE :title')
               ->setParameter('title', '%' . $offerTitle . '%');
        }

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne les candidatures envoyées par un candidat.
     */
    public function findForCandidate(User $candidate, ?string $status = null, ?string $offerTitle = null, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.candidat = :candidate')
            ->setParameter('candidate', $candidate);

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerTitle) {
            $qb->join('a.jobOffre', 'o')
               ->andWhere('o.title LIKE :title')
               ->setParameter('title', '%' . $offerTitle . '%');
        }

        return $qb->orderBy('a.applyDate', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les candidatures envoyées par un candidat.
     */
    public function countForCandidate(User $candidate, ?string $status = null, ?string $offerTitle = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.candidat = :candidate')
            ->setParameter('candidate', $candidate);

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerTitle) {
            $qb->join('a.jobOffre', 'o')
               ->andWhere('o.title LIKE :title')
               ->setParameter('title', '%' . $offerTitle . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
