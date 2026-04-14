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
            ->leftJoin('j.jobApplications', 'a')
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
     * Returns global statistics. If $user is provided, scoped to that user.
     *
     * @return array{total: int, published: int, draft: int, applications: int, types: array}
     */
    public function getStats(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('j');

        if ($user) {
            $qb->andWhere('j.user = :user')->setParameter('user', $user);
        }

        $offres = $qb->getQuery()->getResult();

        $total      = count($offres);
        $published  = 0;
        $draft      = 0;
        $applications = 0;
        $types      = [];

        foreach ($offres as $o) {
            if ($o->getStatus() === 'PUBLISHED') $published++;
            elseif ($o->getStatus() === 'DRAFT') $draft++;
            $applications += $o->getJobApplications()->count();
            $type = $o->getEmploymentType() ?: 'Autre';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }

        return compact('total', 'published', 'draft', 'applications', 'types');
    }
}
