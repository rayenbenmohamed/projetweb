<?php

namespace App\Controller;

use App\Entity\JobOffre;
use App\Entity\OfferStatus;
use App\Repository\JobOffreRepository;
use App\Repository\JobApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/job/offre')]
class JobOffreController extends AbstractController
{
    /** Vue publique : toutes les offres, pour tout le monde */
    #[Route('/', name: 'app_job_offre_index', methods: ['GET'])]
    public function index(JobOffreRepository $jobOffreRepository, JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();
        $userApplications = [];

        if ($user) {
            $applications = $jobApplicationRepository->findBy(['candidat' => $user]);
            foreach ($applications as $app) {
                $userApplications[$app->getJobOffre()->getId()] = $app;
            }
        }

        return $this->render('job_offre/index.html.twig', [
            'job_offres' => $jobOffreRepository->findAll(),
            'user_applications' => $userApplications,
        ]);
    }

    /** Mes offres : seulement les offres du user connecté */
    #[Route('/mes-offres', name: 'app_job_offre_my', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myOffers(JobOffreRepository $jobOffreRepository): Response
    {
        $myOffres = $jobOffreRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('job_offre/my.html.twig', [
            'job_offres' => $myOffres,
        ]);
    }

    #[Route('/new', name: 'app_job_offre_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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
            $jobOffre->setPublishedAt($request->request->get('published_at')
                ? new \DateTime($request->request->get('published_at'))
                : new \DateTime()
            );

            $jobOffre->setUser($this->getUser());

            $entityManager->persist($jobOffre);
            $entityManager->flush();

            $this->addFlash('success', 'Offre créée avec succès.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        return $this->render('job_offre/new.html.twig', ['job_offre' => $jobOffre]);
    }

    #[Route('/{id}', name: 'app_job_offre_show', methods: ['GET'])]
    public function show(JobOffre $jobOffre, JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();
        $userApplication = null;

        if ($user) {
            $userApplication = $jobApplicationRepository->findOneBy([
                'jobOffre' => $jobOffre,
                'candidat' => $user
            ]);
        }

        return $this->render('job_offre/show.html.twig', [
            'job_offre' => $jobOffre,
            'user_application' => $userApplication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_offre_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        // Seul le propriétaire peut modifier
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        if ($request->isMethod('POST')) {
            $jobOffre->setTitle($request->request->get('title'));
            $jobOffre->setDescription($request->request->get('description'));
            $jobOffre->setLocation($request->request->get('location'));
            $jobOffre->setSalary((float) $request->request->get('salary'));
            $jobOffre->setEmploymentType($request->request->get('employment_type'));
            $jobOffre->setAdvantages($request->request->get('advantages'));
            $jobOffre->setSalaryNegotiable($request->request->get('is_salary_negotiable') === 'on');
            $jobOffre->setUpdatedAt(new \DateTime());
            $jobOffre->setExpiresAt(
                $request->request->get('expires_at')
                    ? new \DateTime($request->request->get('expires_at'))
                    : null
            );
            if ($request->request->get('published_at')) {
                $jobOffre->setPublishedAt(new \DateTime($request->request->get('published_at')));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Offre modifiée avec succès.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        return $this->render('job_offre/edit.html.twig', ['job_offre' => $jobOffre]);
    }

    #[Route('/{id}/duplicate', name: 'app_job_offre_duplicate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function duplicate(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('duplicate' . $jobOffre->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_job_offre_my');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez dupliquer que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        $copy = new JobOffre();
        $copy->setTitle('[Copie] ' . $jobOffre->getTitle());
        $copy->setDescription($jobOffre->getDescription());
        $copy->setLocation($jobOffre->getLocation());
        $copy->setSalary($jobOffre->getSalary());
        $copy->setEmploymentType($jobOffre->getEmploymentType());
        $copy->setSalaryNegotiable($jobOffre->isSalaryNegotiable());
        $copy->setAdvantages($jobOffre->getAdvantages());
        $copy->setStatus('DRAFT');
        $copy->setPublishedAt(new \DateTime());
        $copy->setUser($this->getUser());

        $entityManager->persist($copy);
        $entityManager->flush();

        $this->addFlash('success', 'Offre dupliquée avec succès (statut: Brouillon).');
        return $this->redirectToRoute('app_job_offre_my');
    }

    #[Route('/{id}', name: 'app_job_offre_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        if ($this->isCsrfTokenValid('delete' . $jobOffre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($jobOffre);
            $entityManager->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        }

        return $this->redirectToRoute('app_job_offre_my');
    }
}
