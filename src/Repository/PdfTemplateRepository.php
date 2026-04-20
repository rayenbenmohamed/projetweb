<?php

namespace App\Repository;

use App\Entity\PdfTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PdfTemplate>
 *
 * @method PdfTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PdfTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PdfTemplate[]    findAll()
 * @method PdfTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PdfTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfTemplate::class);
    }
}
