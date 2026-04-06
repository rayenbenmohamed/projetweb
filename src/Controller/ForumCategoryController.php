<?php

namespace App\Controller;

use App\Entity\ForumCategory;
use App\Form\ForumCategoryType;
use App\Repository\ForumCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum/category')]
class ForumCategoryController extends AbstractController
{
    #[Route('/', name: 'app_forum_category_index', methods: ['GET'])]
    public function index(ForumCategoryRepository $forumCategoryRepository): Response
    {
        return $this->render('forum_category/index.html.twig', [
            'forum_categories' => $forumCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_forum_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $forumCategory = new ForumCategory();
        $form = $this->createForm(ForumCategoryType::class, $forumCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($forumCategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('forum_category/new.html.twig', [
            'forum_category' => $forumCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_category_show', methods: ['GET'])]
    public function show(int $id, ForumCategoryRepository $repository): Response
    {
        $forumCategory = $repository->find($id);
        if (!$forumCategory) throw $this->createNotFoundException('Category not found');

        return $this->render('forum_category/show.html.twig', [
            'forum_category' => $forumCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_category_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ForumCategoryRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $forumCategory = $repository->find($id);
        if (!$forumCategory) throw $this->createNotFoundException('Category not found');

        $form = $this->createForm(ForumCategoryType::class, $forumCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('forum_category/edit.html.twig', [
            'forum_category' => $forumCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_category_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ForumCategoryRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $forumCategory = $repository->find($id);
        if (!$forumCategory) throw $this->createNotFoundException('Category not found');

        if ($this->isCsrfTokenValid('delete'.$forumCategory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($forumCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forum_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
