<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Entity\JobOffre;
use App\Repository\JobApplicationRepository;
use App\Repository\UserRepository;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_CANDIDAT')]
    public function new(
        Request $request, 
        JobOffre $jobOffre, 
        EntityManagerInterface $entityManager, 
        UserRepository $userRepository,
        CloudinaryService $cloudinaryService
    ): Response {
        $jobApplication = new JobApplication();
        if ($request->isMethod('POST')) {
            $jobApplication->setJobOffre($jobOffre);
            
            // Hardcoded User ID 1 by default until login is functional
            $user = $this->getUser() ?: $userRepository->find(1);
            $jobApplication->setCandidat($user);
            
            $jobApplication->setCoverLetter($request->request->get('cover_letter'));

            // Handle Cloudinary CV upload
            $cvFile = $request->files->get('cv_file');
            if ($cvFile) {
                $cvUrl = $cloudinaryService->uploadFile($cvFile, 'cvs');
                $jobApplication->setCvPath($cvUrl);
            } else {
                $jobApplication->setCvPath($request->request->get('cv_path')); // Fallback from text
            }

            $entityManager->persist($jobApplication);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_offre_index');
        }

        return $this->render('job_application/new.html.twig', [
            'job_offre' => $jobOffre,
        ]);
    }

    #[Route('/{id}', name: 'app_job_application_show', methods: ['GET'])]
    public function show(JobApplication $jobApplication): Response
    {
        return $this->render('job_application/show.html.twig', [
            'job_application' => $jobApplication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $jobApplication->setCoverLetter($request->request->get('cover_letter'));
            $jobApplication->setStatus($request->request->get('status'));

            $entityManager->flush();

            return $this->redirectToRoute('app_job_application_index');
        }

        return $this->render('job_application/edit.html.twig', [
            'job_application' => $jobApplication,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_job_application_delete', methods: ['POST'])]
    public function delete(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $jobApplication->getId(), $request->request->get('_token'))) {
            $entityManager->remove($jobApplication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_job_application_index');
    }

    #[Route('/{id}/status', name: 'app_job_application_status', methods: ['POST'])]
    public function updateStatus(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        $jobApplication->setStatus($status);
        $entityManager->flush();

        if ($status === 'ACCEPTED') {
            return $this->redirectToRoute('app_interview_new', ['applicationId' => $jobApplication->getId()]);
        }

        return $this->redirectToRoute('app_job_application_index');
    }
}
