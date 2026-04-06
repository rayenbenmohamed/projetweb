<?php

namespace App\Repository;

use App\Entity\FriendMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FriendMessage>
 */
class FriendMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendMessage::class);
    }

    /**
     * @return FriendMessage[]
     */
    public function findConversation(User $a, User $b, int $limit = 100): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :a AND m.recipient = :b) OR (m.sender = :b AND m.recipient = :a)')
            ->setParameter('a', $a)
            ->setParameter('b', $b)
            ->orderBy('m.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadForRecipient(User $recipient): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.recipient = :u')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('u', $recipient)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, int> expéditeur (id) => nombre de messages non lus
     */
    public function unreadCountsBySender(User $recipient): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('IDENTITY(m.sender) AS sid')
            ->addSelect('COUNT(m.id) AS cnt')
            ->where('m.recipient = :u')
            ->andWhere('m.readAt IS NULL')
            ->groupBy('m.sender')
            ->setParameter('u', $recipient)
            ->getQuery()
            ->getScalarResult();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['sid']] = (int) $row['cnt'];
        }

        return $out;
    }

    public function markConversationReadForRecipient(User $recipient, User $sender): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(FriendMessage::class, 'm')
            ->set('m.readAt', ':now')
            ->where('m.recipient = :me')
            ->andWhere('m.sender = :peer')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('me', $recipient)
            ->setParameter('peer', $sender)
            ->getQuery()
            ->execute();
    }
}
