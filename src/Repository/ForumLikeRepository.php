<?php

namespace App\Repository;

use App\Entity\ForumLike;
use App\Entity\ForumPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumLike>
 */
class ForumLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumLike::class);
    }

    public function findOneByPostAndUser(ForumPost $post, User $user): ?ForumLike
    {
        return $this->findOneBy([
            'post' => $post,
            'user' => $user,
        ]);
    }
}
