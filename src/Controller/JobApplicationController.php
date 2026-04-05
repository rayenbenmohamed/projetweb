<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Entity\JobOffre;
use App\Repository\JobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job/application')]
class JobApplicationController extends AbstractController
{
    #[Route('/', name: 'app_job_application_index', methods: ['GET'])]
    public function index(JobApplicationRepository $jobApplicationRepository): Response
    {
        // For recruiters: see applications for their jobs
        // For candidates: see their own applications
        return $this->render('job_application/index.html.twig', [
            'job_applications' => $jobApplicationRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_job_application_new', methods: ['GET', 'POST'])]
    public function new(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        $jobApplication = new JobApplication();
        if ($request->isMethod('POST')) {
            $jobApplication->setJobOffre($jobOffre);
            $jobApplication->setCandidat($this->getUser());
            $jobApplication->setCoverLetter($request->request->get('cover_letter'));

            // Handle CV upload path for now as a string
            $jobApplication->setCvPath($request->request->get('cv_path'));

            $entityManager->persist($jobApplication);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offre_index');
        }

        return $this->render('job_application/new.html.twig', [
            'job_offre' => $jobOffre,
        ]);
    }

    #[Route('/{id}/status', name: 'app_job_application_status', methods: ['POST'])]
    public function updateStatus(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        $jobApplication->setStatus($status);
        $entityManager->flush();

        return $this->redirectToRoute('app_job_application_index');
    }
}
