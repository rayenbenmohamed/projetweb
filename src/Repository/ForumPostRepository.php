<?php

namespace App\Repository;

use App\Entity\ForumPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumPost>
 */
class ForumPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumPost::class);
    }

    public function searchAndFilter(?string $query, ?int $categoryId, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'cm')
            ->leftJoin('p.likes', 'l')
            ->addSelect('c')
            ->groupBy('p.id');

        if ($query) {
            $qb->andWhere('p.title LIKE :query OR p.content LIKE :query')
                ->setParameter('query', '%' . trim($query) . '%');
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        switch ($sort) {
            case 'popular':
                $qb->orderBy('COUNT(DISTINCT cm.id)', 'DESC')
                    ->addOrderBy('COUNT(DISTINCT l.id)', 'DESC')
                    ->addOrderBy('p.createdAt', 'DESC');
                break;
            case 'most_liked':
                $qb->orderBy('COUNT(DISTINCT l.id)', 'DESC')
                    ->addOrderBy('p.createdAt', 'DESC');
                break;
            default:
                $qb->orderBy('p.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
