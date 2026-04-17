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
    public function findByRecruiter(User $recruiter, ?string $status = null, ?string $offerName = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.jobOffre', 'o')
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

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne toutes les candidatures d'un candidat, avec filtres optionnels.
     */
    public function findForCandidate(User $candidate, ?string $status = null, ?string $offerName = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.jobOffre', 'o') // left join pour au cas où l'offre n'est pas chargée directement, mais utile pour la recherche
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

        return $qb->getQuery()->getResult();
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
