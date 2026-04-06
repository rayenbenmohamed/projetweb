<?php

namespace App\Controller;

use App\Entity\JobOffre;
use App\Entity\OfferStatus;
use App\Repository\JobOffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job/offre')]
class JobOffreController extends AbstractController
{
    #[Route('/', name: 'app_job_offre_index', methods: ['GET'])]
    public function index(JobOffreRepository $jobOffreRepository): Response
    {
        return $this->render('job_offre/index.html.twig', [
            'job_offres' => $jobOffreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_job_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jobOffre = new JobOffre();
        if ($request->isMethod('POST')) {
            $jobOffre->setTitle($request->request->get('title'));
            $jobOffre->setDescription($request->request->get('description'));
            $jobOffre->setLocation($request->request->get('location'));
            $jobOffre->setSalary((float) $request->request->get('salary'));
            $jobOffre->setEmploymentType($request->request->get('employment_type'));
            $jobOffre->setStatus(OfferStatus::PUBLISHED->value);
            $jobOffre->setUser($this->getUser());

            $entityManager->persist($jobOffre);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offre_index');
        }

        return $this->render('job_offre/new.html.twig', [
            'job_offre' => $jobOffre,
        ]);
    }

    #[Route('/{id}', name: 'app_job_offre_show', methods: ['GET'])]
    public function show(JobOffre $jobOffre): Response
    {
        return $this->render('job_offre/show.html.twig', [
            'job_offre' => $jobOffre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $jobOffre->setTitle($request->request->get('title'));
            $jobOffre->setDescription($request->request->get('description'));
            $jobOffre->setLocation($request->request->get('location'));
            $jobOffre->setSalary((float) $request->request->get('salary'));
            $jobOffre->setEmploymentType($request->request->get('employment_type'));

            $entityManager->flush();

            return $this->redirectToRoute('app_job_offre_index');
        }

        return $this->render('job_offre/edit.html.twig', [
            'job_offre' => $jobOffre,
        ]);
    }

    #[Route('/{id}', name: 'app_job_offre_delete', methods: ['POST'])]
    public function delete(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $jobOffre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($jobOffre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_job_offre_index');
    }
}
