<?php

namespace App\Controller;

use App\Entity\CoverLetter;
use App\Form\CoverLetterType;
use App\Repository\CoverLetterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cover-letter')]
class CoverLetterController extends AbstractController
{
    #[Route('/', name: 'app_cover_letter_index', methods: ['GET'])]
    public function index(CoverLetterRepository $coverLetterRepository): Response
    {
        return $this->render('cover_letter/index.html.twig', [
            'cover_letters' => $coverLetterRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cover_letter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $coverLetter = new CoverLetter();
        $form = $this->createForm(CoverLetterType::class, $coverLetter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($coverLetter);
            $entityManager->flush();

            return $this->redirectToRoute('app_cover_letter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cover_letter/new.html.twig', [
            'cover_letter' => $coverLetter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cover_letter_show', methods: ['GET'])]
    public function show(CoverLetter $coverLetter): Response
    {
        return $this->render('cover_letter/show.html.twig', [
            'cover_letter' => $coverLetter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cover_letter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CoverLetter $coverLetter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoverLetterType::class, $coverLetter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverLetter->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_cover_letter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cover_letter/edit.html.twig', [
            'cover_letter' => $coverLetter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cover_letter_delete', methods: ['POST'])]
    public function delete(Request $request, CoverLetter $coverLetter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coverLetter->getId(), $request->request->get('_token'))) {
            $entityManager->remove($coverLetter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cover_letter_index', [], Response::HTTP_SEE_OTHER);
    }
}
