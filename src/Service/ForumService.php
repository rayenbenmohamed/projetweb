<?php

namespace App\Service;

use App\Entity\ForumPost;
use App\Entity\ForumComment;
use App\Entity\ForumLike;
use App\Entity\User;
use App\Repository\ForumLikeRepository;
use App\Repository\ForumPostRepository;
use App\Repository\ForumCommentRepository;
use Doctrine\ORM\EntityManagerInterface;

class ForumService
{
    private EntityManagerInterface $entityManager;
    private ForumPostRepository $forumPostRepository;
    private ForumCommentRepository $forumCommentRepository;
    private ForumLikeRepository $forumLikeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ForumPostRepository $forumPostRepository,
        ForumCommentRepository $forumCommentRepository,
        ForumLikeRepository $forumLikeRepository
    ) {
        $this->entityManager = $entityManager;
        $this->forumPostRepository = $forumPostRepository;
        $this->forumCommentRepository = $forumCommentRepository;
        $this->forumLikeRepository = $forumLikeRepository;
    }

    public function savePost(ForumPost $post, User $user): void
    {
        $post->setUser($user);
        $post->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }

    public function addComment(ForumPost $post, string $content, User $user): ForumComment
    {
        $comment = new ForumComment();
        $comment->setPost($post);
        $comment->setContent($content);
        $comment->setUser($user);
        $comment->setCreatedAt(new \DateTime());

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    public function deletePost(ForumPost $post): void
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function deleteComment(ForumComment $comment): void
    {
        $this->entityManager->remove($comment);
        $this->entityManager->flush();
    }

    public function getAllPosts(): array
    {
        return $this->forumPostRepository->searchAndFilter(null, null, 'recent');
    }

    public function getFilteredPosts(?string $query, ?int $categoryId, ?string $sort): array
    {
        return $this->forumPostRepository->searchAndFilter($query, $categoryId, $sort);
    }

    public function updatePost(ForumPost $post): void
    {
        $post->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
    }

    public function toggleLike(ForumPost $post, User $user): bool
    {
        $existingLike = $this->forumLikeRepository->findOneByPostAndUser($post, $user);

        if ($existingLike) {
            $this->entityManager->remove($existingLike);
            $this->entityManager->flush();
            return false;
        }

        $like = new ForumLike();
        $like->setPost($post);
        $like->setUser($user);
        $this->entityManager->persist($like);
        $this->entityManager->flush();

        return true;
    }
}
