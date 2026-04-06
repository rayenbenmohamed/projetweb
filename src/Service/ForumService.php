<?php

namespace App\Service;

use App\Entity\ForumPost;
use App\Entity\ForumComment;
use App\Entity\User;
use App\Repository\ForumPostRepository;
use App\Repository\ForumCommentRepository;
use Doctrine\ORM\EntityManagerInterface;

class ForumService
{
    private EntityManagerInterface $entityManager;
    private ForumPostRepository $forumPostRepository;
    private ForumCommentRepository $forumCommentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ForumPostRepository $forumPostRepository,
        ForumCommentRepository $forumCommentRepository
    ) {
        $this->entityManager = $entityManager;
        $this->forumPostRepository = $forumPostRepository;
        $this->forumCommentRepository = $forumCommentRepository;
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

    public function getAllPosts(): array
    {
        return $this->forumPostRepository->findAll();
    }

    public function updatePost(ForumPost $post): void
    {
        $post->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
    }
}
