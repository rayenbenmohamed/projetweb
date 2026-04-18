<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countPendingRecruiters(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u INSTANCE OF App\Entity\Recruiter')
            ->andWhere('u.approved = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return User[]
     */
    public function findPendingRecruiters(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u INSTANCE OF App\Entity\Recruiter')
            ->andWhere('u.approved = false')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
