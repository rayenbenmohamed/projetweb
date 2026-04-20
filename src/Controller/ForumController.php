<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Entity\ForumComment;
use App\Form\ForumPostType;
use App\Repository\ForumCategoryRepository;
use App\Service\AiCommentService;
use App\Service\BadWordModerationService;
use App\Service\ForumService;
use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    private ForumService $forumService;
    private \Doctrine\ORM\EntityManagerInterface $em;
    private BadWordModerationService $moderationService;
    private AiCommentService $aiCommentService;
    private TranslationService $translationService;
    private ForumCategoryRepository $forumCategoryRepository;

    public function __construct(
        ForumService $forumService,
        \Doctrine\ORM\EntityManagerInterface $em,
        BadWordModerationService $moderationService,
        AiCommentService $aiCommentService,
        TranslationService $translationService,
        ForumCategoryRepository $forumCategoryRepository
    )
    {
        $this->forumService = $forumService;
        $this->em = $em;
        $this->moderationService = $moderationService;
        $this->aiCommentService = $aiCommentService;
        $this->translationService = $translationService;
        $this->forumCategoryRepository = $forumCategoryRepository;
    }

    private function getDevUser(): \App\Entity\User
    {
        $user = $this->getUser();
        if ($user) {
            return $user;
        }
        
        $user = $this->em->getRepository(\App\Entity\User::class)->findOneBy([]);
        if (!$user) {
            $user = new \App\Entity\User();
            $user->setEmail('admin@test.com');
            $user->setPassword('admin123');
            $user->setRole('admin');
            $this->em->persist($user);
            $this->em->flush();
        }
        
        return $user;
    }

    #[Route('/', name: 'app_forum_index')]
    public function index(Request $request): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $categoryId = $request->query->getInt('category');
        $sort = (string) $request->query->get('sort', 'recent');

        return $this->render('forum/index.html.twig', [
            'posts' => $this->forumService->getFilteredPosts(
                $query !== '' ? $query : null,
                $categoryId > 0 ? $categoryId : null,
                $sort
            ),
            'categories' => $this->forumCategoryRepository->findBy([], ['name' => 'ASC']),
            'filters' => [
                'q' => $query,
                'category' => $categoryId,
                'sort' => $sort,
            ],
        ]);
    }

    #[Route('/post/{id}', name: 'app_forum_post_show')]
    public function show(int $id): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'currentUser' => $this->getDevUser(),
        ]);
    }

    #[Route('/post/{id}/comment', name: 'app_forum_comment_add', methods: ['POST'])]
    public function addComment(int $id, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        if (!$this->isCsrfTokenValid('add_comment', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $content = trim((string) $request->request->get('content', ''));
        if ($content) {
            $moderation = $this->moderationService->detect($content);
            if ($moderation['containsBadWords']) {
                $this->addFlash('danger', 'Comment blocked: please remove inappropriate words.');
                return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
            }

            $this->forumService->addComment($post, $content, $this->getDevUser());
            $this->addFlash('success', 'Comment added successfully.');
        }

        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
    }

    #[Route('/post/{postId}/comment/{commentId}/delete', name: 'app_forum_comment_delete', methods: ['POST'])]
    public function deleteComment(int $postId, int $commentId, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comment = $this->em->getRepository(ForumComment::class)->find($commentId);
        if (!$comment || !$comment->getPost() || $comment->getPost()->getId() !== $post->getId()) {
            throw $this->createNotFoundException('Comment not found');
        }

        if (!$this->isCsrfTokenValid('delete_comment_' . $comment->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $this->forumService->deleteComment($comment);
        $this->addFlash('success', 'Comment deleted.');

        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
    }

    #[Route('/post/{id}/like', name: 'app_forum_post_like_toggle', methods: ['POST'])]
    public function toggleLike(int $id, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        if (!$this->isCsrfTokenValid('toggle_like_' . $post->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $liked = $this->forumService->toggleLike($post, $this->getDevUser());
        $this->addFlash('success', $liked ? 'Post liked.' : 'Like removed.');

        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
    }

    #[Route('/api/moderate-comment', name: 'app_forum_api_moderate_comment', methods: ['POST'])]
    public function moderateCommentApi(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $content = trim((string) ($payload['content'] ?? ''));

        if ($content === '') {
            return $this->json([
                'containsBadWords' => false,
                'badWords' => [],
                'sanitizedText' => '',
            ]);
        }

        return $this->json($this->moderationService->detect($content));
    }

    #[Route('/api/post/{id}/ai-comment', name: 'app_forum_api_ai_comment', methods: ['POST'])]
    public function aiCommentApi(int $id, Request $request): JsonResponse
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $prompt = trim((string) ($payload['prompt'] ?? ''));
        $suggestion = $this->aiCommentService->generateCommentSuggestion($post, $prompt !== '' ? $prompt : null);

        return $this->json([
            'suggestion' => $suggestion,
            'source' => $this->aiCommentService->getLastSource(),
            'error' => $this->aiCommentService->getLastError(),
        ]);
    }

    #[Route('/api/translate-to-english', name: 'app_forum_api_translate_to_english', methods: ['POST'])]
    public function translateToEnglishApi(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $text = trim((string) ($payload['text'] ?? ''));

        $result = $this->translationService->translateToEnglish($text);

        return $this->json($result);
    }

    #[Route('/new', name: 'app_forum_post_new')]
    public function new(Request $request): Response
    {
        $post = new ForumPost();
        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/forum',
                    $newFilename
                );
                $post->setImagePath($newFilename);
            }
            $this->forumService->savePost($post, $this->getDevUser());
            return $this->redirectToRoute('app_forum_index');
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_forum_post_edit')]
    public function edit(int $id, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        $form = $this->createForm(ForumPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/forum',
                    $newFilename
                );
                $post->setImagePath($newFilename);
            }
            $this->forumService->updatePost($post);
            return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_forum_post_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->forumService->deletePost($post);
        }

        return $this->redirectToRoute('app_forum_index');
    }
}
