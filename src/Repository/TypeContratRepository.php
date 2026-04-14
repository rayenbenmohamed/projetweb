<?php

namespace App\Repository;

use App\Entity\TypeContrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeContrat>
 */
class TypeContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeContrat::class);
    }

    /**
     * @return array{items: TypeContrat[], total: int, pages: int, page: int, limit: int}
     */
    public function searchPaginated(string $q = '', int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $q = trim($q);

        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC');

        if ($q !== '') {
            $qb->andWhere('LOWER(t.name) LIKE :q OR LOWER(COALESCE(t.description, \'\')) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $paginator = new Paginator($qb, true);
        $total = count($paginator);
        $pages = (int) max(1, (int) ceil($total / $limit));

        return [
            'items' => iterator_to_array($paginator->getIterator()),
            'total' => $total,
            'pages' => $pages,
            'page' => min($page, $pages),
            'limit' => $limit,
        ];
    }
}
