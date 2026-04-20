<?php

namespace App\Repository;

use App\Entity\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @return array{items: Contract[], total: int, pages: int, page: int, limit: int}
     */
    public function searchPaginated(array $filters, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $q = trim((string) ($filters['q'] ?? ''));
        $typeId = $filters['type'] ?? null;
        $status = trim((string) ($filters['status'] ?? ''));
        $signed = $filters['signed'] ?? null;
        $salaryMin = $filters['salary_min'] ?? null;
        $candidateId = $filters['candidate_id'] ?? null;
        $recruiterId = $filters['recruiter_id'] ?? null;

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.candidate', 'candidate')->addSelect('candidate')
            ->leftJoin('c.recruiter', 'recruiter')->addSelect('recruiter')
            ->leftJoin('c.jobOffre', 'jobOffre')->addSelect('jobOffre')
            ->leftJoin('c.typeContrat', 'type')->addSelect('type')
            ->orderBy('c.id', 'DESC');

        if ($q !== '') {
            $orX = $qb->expr()->orX();
            $orX->add('LOWER(candidate.email) LIKE :q');
            $orX->add('LOWER(COALESCE(candidate.firstName, \'\')) LIKE :q');
            $orX->add('LOWER(COALESCE(candidate.lastName, \'\')) LIKE :q');
            $orX->add('LOWER(COALESCE(jobOffre.title, \'\')) LIKE :q');
            
            if (is_numeric($q)) {
                $orX->add('c.id = :qId');
                $qb->setParameter('qId', (int) $q);
            }
            
            $qb->andWhere($orX)
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        if ($typeId !== null && $typeId !== '' && ctype_digit((string) $typeId)) {
            $qb->andWhere('type.id = :typeId')->setParameter('typeId', (int) $typeId);
        }

        if ($status !== '') {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        if ($signed !== null && $signed !== '' && in_array((string) $signed, ['0', '1'], true)) {
            $qb->andWhere('c.isSigned = :signed')->setParameter('signed', (bool) ((int) $signed));
        }

        if ($salaryMin !== null && $salaryMin !== '' && is_numeric($salaryMin)) {
            $qb->andWhere('c.salary >= :salaryMin')->setParameter('salaryMin', (int) $salaryMin);
        }

        if ($candidateId !== null && $candidateId !== '' && ctype_digit((string) $candidateId)) {
            $qb->andWhere('candidate.id = :candidateId')->setParameter('candidateId', (int) $candidateId);
        }

        if ($recruiterId !== null && $recruiterId !== '' && ctype_digit((string) $recruiterId)) {
            $qb->andWhere('recruiter.id = :recruiterId')->setParameter('recruiterId', (int) $recruiterId);
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
