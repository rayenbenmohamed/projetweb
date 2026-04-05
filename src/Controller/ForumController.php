<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Service\ForumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    private ForumService $forumService;

    public function __construct(ForumService $forumService)
    {
        $this->forumService = $forumService;
    }

    #[Route('/', name: 'app_forum_index')]
    public function index(): Response
    {
        // For now, just render a placeholder or a list of posts
        return $this->render('forum/index.html.twig', [
            'posts' => [], // In a real app, Fetch from service
        ]);
    }

    #[Route('/post/{id}', name: 'app_forum_post_show')]
    public function show(ForumPost $post): Response
    {
        return $this->render('forum/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/comment', name: 'app_forum_comment_add', methods: ['POST'])]
    public function addComment(Request $request, ForumPost $post): Response
    {
        $content = $request->request->get('content');
        if ($content) {
            $this->forumService->addComment($post, $content);
        }

        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
    }
}
