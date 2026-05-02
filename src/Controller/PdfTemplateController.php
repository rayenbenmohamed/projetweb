<?php

namespace App\Controller;

use App\Entity\PdfTemplate;
use App\Form\PdfTemplateType;
use App\Repository\PdfTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/pdf-template')]
class PdfTemplateController extends AbstractController
{
    #[Route('/', name: 'app_pdf_template_index', methods: ['GET'])]
    public function index(PdfTemplateRepository $pdfTemplateRepository): Response
    {
        return $this->render('pdf_template/index.html.twig', [
            'pdf_templates' => $pdfTemplateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pdf_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $pdfTemplate = new PdfTemplate();
        $form = $this->createForm(PdfTemplateType::class, $pdfTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logoFile')->getData();
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                        $newFilename
                    );
                    $pdfTemplate->setLogoPath('/uploads/logos/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                }
            }

            $entityManager->persist($pdfTemplate);
            $entityManager->flush();

            return $this->redirectToRoute('app_pdf_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pdf_template/new.html.twig', [
            'pdf_template' => $pdfTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pdf_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PdfTemplate $pdfTemplate, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PdfTemplateType::class, $pdfTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logoFile')->getData();
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                        $newFilename
                    );
                    $pdfTemplate->setLogoPath('/uploads/logos/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du logo : ' . $e->getMessage());
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Modèle mis à jour avec succès.');
            return $this->redirectToRoute('app_pdf_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pdf_template/edit.html.twig', [
            'pdf_template' => $pdfTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pdf_template_delete', methods: ['POST'])]
    public function delete(Request $request, PdfTemplate $pdfTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pdfTemplate->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pdfTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pdf_template_index', [], Response::HTTP_SEE_OTHER);
    }
}
