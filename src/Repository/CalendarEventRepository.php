<?php

namespace App\Repository;

use App\Entity\CalendarEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarEvent>
 */
class CalendarEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarEvent::class);
    }

    /**
     * @return CalendarEvent[]
     */
    public function findUpcomingForUser(User $user, int $limit = 200): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.startAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CalendarEvent[]
     */
    public function findForUserBetween(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.startAt < :end')
            ->andWhere('(e.endAt IS NULL AND e.startAt >= :start) OR (e.endAt IS NOT NULL AND e.endAt >= :start)')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

