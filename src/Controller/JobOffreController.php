<?php

namespace App\Controller;

use App\Entity\JobOffre;
use App\Entity\OfferStatus;
use App\Repository\JobOffreRepository;
use App\Repository\UserRepository;
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
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $jobOffre = new JobOffre();
        if ($request->isMethod('POST')) {
            $jobOffre->setTitle($request->request->get('title'));
            $jobOffre->setDescription($request->request->get('description'));
            $jobOffre->setLocation($request->request->get('location'));
            $jobOffre->setSalary((float) $request->request->get('salary'));
            $jobOffre->setEmploymentType($request->request->get('employment_type'));
            $jobOffre->setStatus($request->request->get('status', OfferStatus::PUBLISHED->value));
            $jobOffre->setAdvantages($request->request->get('advantages'));
            $jobOffre->setSalaryNegotiable($request->request->get('is_salary_negotiable') === 'on');
            
            if ($request->request->get('expires_at')) {
                $jobOffre->setExpiresAt(new \DateTime($request->request->get('expires_at')));
            }

            if ($request->request->get('published_at')) {
                $jobOffre->setPublishedAt(new \DateTime($request->request->get('published_at')));
            } else {
                $jobOffre->setPublishedAt(new \DateTime());
            }
            
            // Hardcoded User ID 1 by default until login is functional
            $user = $this->getUser() ?: $userRepository->find(1);
            $jobOffre->setUser($user);

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
    public function edit(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $jobOffre->setTitle($request->request->get('title'));
            $jobOffre->setDescription($request->request->get('description'));
            $jobOffre->setLocation($request->request->get('location'));
            $jobOffre->setSalary((float) $request->request->get('salary'));
            $jobOffre->setEmploymentType($request->request->get('employment_type'));
            $jobOffre->setAdvantages($request->request->get('advantages'));
            $jobOffre->setSalaryNegotiable($request->request->get('is_salary_negotiable') === 'on');
            $jobOffre->setUpdatedAt(new \DateTime());
            
            if ($request->request->get('expires_at')) {
                $jobOffre->setExpiresAt(new \DateTime($request->request->get('expires_at')));
            } else {
                $jobOffre->setExpiresAt(null);
            }

            if ($request->request->get('published_at')) {
                $jobOffre->setPublishedAt(new \DateTime($request->request->get('published_at')));
            }

            if (!$jobOffre->getUser()) {
                $jobOffre->setUser($userRepository->find(1));
            }

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
