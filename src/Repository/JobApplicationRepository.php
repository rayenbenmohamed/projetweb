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
     * Retourne toutes les candidatures reçues sur les offres créées par ce recruteur, avec filtres optionnels.
     */
    public function findByRecruiter(User $recruiter, ?string $status = null, ?string $offerName = null, int $limit = 10, int $offset = 0): array
    {
        return $this->getQueryByRecruiter($recruiter, $status, $offerName)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getQueryByRecruiter(User $recruiter, ?string $status = null, ?string $offerName = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter)
            ->orderBy('a.aiScore', 'DESC')
            ->addOrderBy('a.applyDate', 'DESC');

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerName) {
            $qb->andWhere('o.title LIKE :offerName')
               ->setParameter('offerName', '%' . $offerName . '%');
        }

        return $qb;
    }

    /**
     * Retourne toutes les candidatures d'un candidat, avec filtres optionnels.
     */
    public function findForCandidate(User $candidate, ?string $status = null, ?string $offerName = null, int $limit = 10, int $offset = 0): array
    {
        return $this->getQueryForCandidate($candidate, $status, $offerName)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getQueryForCandidate(User $candidate, ?string $status = null, ?string $offerName = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.jobOffre', 'o') 
            ->where('a.candidat = :candidate')
            ->setParameter('candidate', $candidate)
            ->orderBy('a.applyDate', 'DESC');

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerName) {
            $qb->andWhere('o.title LIKE :offerName')
               ->setParameter('offerName', '%' . $offerName . '%');
        }

        return $qb;
    }

    /**
     * Compte toutes les candidatures reçues sur les offres créées par ce recruteur.
     */
    /**
     * Compte les candidatures reçues sur les offres d'un recruteur avec filtres.
     */
    public function countByRecruiter(User $recruiter, ?string $status = null, ?string $offerName = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.jobOffre', 'o')
            ->where('o.user = :recruiter')
            ->setParameter('recruiter', $recruiter);

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerName) {
            $qb->andWhere('o.title LIKE :offerName')
               ->setParameter('offerName', '%' . $offerName . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Compte les candidatures envoyées par un candidat avec filtres.
     */
    public function countForCandidate(User $candidate, ?string $status = null, ?string $offerName = null): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->leftJoin('a.jobOffre', 'o')
            ->where('a.candidat = :candidate')
            ->setParameter('candidate', $candidate);

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($offerName) {
            $qb->andWhere('o.title LIKE :offerName')
               ->setParameter('offerName', '%' . $offerName . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
