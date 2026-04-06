<?php

namespace App\Controller;

use App\Entity\ForumPost;
use App\Form\ForumPostType;
use App\Service\ForumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    private ForumService $forumService;
    private \Doctrine\ORM\EntityManagerInterface $em;

    public function __construct(ForumService $forumService, \Doctrine\ORM\EntityManagerInterface $em)
    {
        $this->forumService = $forumService;
        $this->em = $em;
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
    public function index(): Response
    {
        return $this->render('forum/index.html.twig', [
            'posts' => $this->forumService->getAllPosts(),
        ]);
    }

    #[Route('/post/{id}', name: 'app_forum_post_show')]
    public function show(int $id): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        return $this->render('forum/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/{id}/comment', name: 'app_forum_comment_add', methods: ['POST'])]
    public function addComment(int $id, Request $request): Response
    {
        $post = $this->em->getRepository(ForumPost::class)->find($id);
        if (!$post) throw $this->createNotFoundException('Post not found');

        $content = $request->request->get('content');
        if ($content) {
            $this->forumService->addComment($post, $content, $this->getDevUser());
        }

        return $this->redirectToRoute('app_forum_post_show', ['id' => $post->getId()]);
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
