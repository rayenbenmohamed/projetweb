<?php

namespace App\Repository;

use App\Entity\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    public function findByFilters(?string $search, ?string $status, ?int $typeId, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.candidate', 'can')
            ->leftJoin('c.typeContrat', 'tc')
            ->leftJoin('c.jobOffre', 'jo');

        if ($search) {
            $qb->andWhere('can.email LIKE :search OR jo.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($typeId) {
            $qb->andWhere('tc.id = :typeId')
                ->setParameter('typeId', $typeId);
        }

        return $qb->orderBy('c.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByFilters(?string $search, ?string $status, ?int $typeId): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->leftJoin('c.candidate', 'can')
            ->leftJoin('c.typeContrat', 'tc')
            ->leftJoin('c.jobOffre', 'jo');

        if ($search) {
            $qb->andWhere('can.email LIKE :search OR jo.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($typeId) {
            $qb->andWhere('tc.id = :typeId')
                ->setParameter('typeId', $typeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
