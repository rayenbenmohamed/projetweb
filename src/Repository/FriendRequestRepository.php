<?php

namespace App\Repository;

use App\Entity\FriendRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FriendRequest>
 */
class FriendRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendRequest::class);
    }

    public function findBetweenUsers(User $a, User $b): ?FriendRequest
    {
        $qb = $this->createQueryBuilder('f')
            ->where('(f.sender = :a AND f.receiver = :b) OR (f.sender = :b AND f.receiver = :a)')
            ->setParameter('a', $a)
            ->setParameter('b', $b)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return FriendRequest[]
     */
    public function findAcceptedForUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :accepted')
            ->andWhere('f.sender = :u OR f.receiver = :u')
            ->setParameter('accepted', FriendRequest::STATUS_ACCEPTED)
            ->setParameter('u', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return FriendRequest[]
     */
    public function findIncomingPending(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :p')
            ->andWhere('f.receiver = :u')
            ->setParameter('p', FriendRequest::STATUS_PENDING)
            ->setParameter('u', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countIncomingPending(User $user): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.status = :p')
            ->andWhere('f.receiver = :u')
            ->setParameter('p', FriendRequest::STATUS_PENDING)
            ->setParameter('u', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return FriendRequest[]
     */
    public function findOutgoingPending(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :p')
            ->andWhere('f.sender = :u')
            ->setParameter('p', FriendRequest::STATUS_PENDING)
            ->setParameter('u', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
