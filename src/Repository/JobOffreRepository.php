<?php

namespace App\Repository;

use App\Entity\JobOffre;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffre>
 */
class JobOffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffre::class);
    }

    /**
     * Search / filter job offers.
     *
     * @param array{
     *   q?: string,
     *   type?: string,
     *   status?: string,
     *   location?: string,
     *   salary_min?: float|null,
     *   salary_max?: float|null,
     *   user?: User|null,
     * } $criteria
     * @return JobOffre[]
     */
    public function search(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('j')
            ->orderBy('j.createdAt', 'DESC');

        if (!empty($criteria['user'])) {
            $qb->andWhere('j.user = :user')->setParameter('user', $criteria['user']);
        }

        if (!empty($criteria['q'])) {
            $qb->andWhere('j.title LIKE :q OR j.location LIKE :q OR j.description LIKE :q')
               ->setParameter('q', '%' . $criteria['q'] . '%');
        }

        if (!empty($criteria['type'])) {
            $qb->andWhere('j.employmentType = :type')->setParameter('type', $criteria['type']);
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('j.status = :status')->setParameter('status', $criteria['status']);
        }

        // Always filter out expired offers for public listing (when status is PUBLISHED and no user is specified)
        if (empty($criteria['user']) && (empty($criteria['status']) || $criteria['status'] === 'PUBLISHED')) {
            $qb->andWhere('j.expiresAt IS NULL OR j.expiresAt > :now')
               ->setParameter('now', new \DateTime());
        }

        if (!empty($criteria['location'])) {
            $qb->andWhere('j.location LIKE :location')->setParameter('location', '%' . $criteria['location'] . '%');
        }

        if (isset($criteria['salary_min']) && $criteria['salary_min'] !== null && $criteria['salary_min'] !== '') {
            $qb->andWhere('j.salary >= :salary_min')->setParameter('salary_min', (float) $criteria['salary_min']);
        }

        if (isset($criteria['salary_max']) && $criteria['salary_max'] !== null && $criteria['salary_max'] !== '') {
            $qb->andWhere('j.salary <= :salary_max')->setParameter('salary_max', (float) $criteria['salary_max']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns global statistics using optimised SQL COUNT queries.
     * Avoids loading all entities + their collections into memory.
     *
     * @return array{total: int, published: int, draft: int, applications: int, types: array}
     */
    public function getStats(?User $user = null): array
    {
        $em = $this->getEntityManager();
        $userCondition = $user ? 'AND j.user = :user' : '';

        // Single query: total + published + draft
        $countsQb = $this->createQueryBuilder('j')
            ->select(
                'COUNT(j.id) AS total',
                "SUM(CASE WHEN j.status = 'PUBLISHED' AND (j.expiresAt IS NULL OR j.expiresAt > CURRENT_TIMESTAMP()) THEN 1 ELSE 0 END) AS published",
                "SUM(CASE WHEN j.status = 'DRAFT' THEN 1 ELSE 0 END) AS draft"
            );

        if ($user) {
            $countsQb->where('j.user = :user')->setParameter('user', $user);
        }

        $row = $countsQb->getQuery()->getSingleResult();

        // Count applications via join (one query)
        $appsQb = $em->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from('App\Entity\JobApplication', 'a')
            ->join('a.jobOffre', 'j');

        if ($user) {
            $appsQb->where('j.user = :user')->setParameter('user', $user);
        }

        $applications = (int) $appsQb->getQuery()->getSingleScalarResult();

        // Employment type breakdown
        $typesQb = $this->createQueryBuilder('j')
            ->select('j.employmentType AS type', 'COUNT(j.id) AS cnt')
            ->groupBy('j.employmentType');

        if ($user) {
            $typesQb->where('j.user = :user')->setParameter('user', $user);
        }

        $types = [];
        foreach ($typesQb->getQuery()->getResult() as $t) {
            $types[$t['type'] ?: 'Autre'] = (int) $t['cnt'];
        }

        return [
            'total'        => (int) ($row['total'] ?? 0),
            'published'    => (int) ($row['published'] ?? 0),
            'draft'        => (int) ($row['draft'] ?? 0),
            'applications' => $applications,
            'types'        => $types,
        ];
    }
}
