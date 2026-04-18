<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Entity\JobApplication;
use App\Service\InterviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/interview')]
class InterviewController extends AbstractController
{
    private InterviewService $interviewService;

    public function __construct(InterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    #[Route('/new/{jobApplication}', name: 'app_interview_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function new(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($jobApplication->getJobOffre()->getUser() !== $user) {
            $this->addFlash('danger', 'Accès refusé.');
            return $this->redirectToRoute('app_job_application_index');
        }

        if ($request->isMethod('POST')) {
            $dateStr = $request->request->get('scheduledAt');
            $notes = $request->request->get('notes');
            $meetingLink = $request->request->get('meetingLink');

            // Validation manuelle (car symfony/validator n'est pas présent dans ce projet)
            $errors = [];
            if (!$dateStr) $errors[] = "La date est obligatoire.";
            if (strlen($notes) < 10) $errors[] = "Les notes doivent faire au moins 10 caractères.";
            if (!$meetingLink) $meetingLink = "Entretien Vidéo Interne";

            $date = null;
            try {
                $date = $dateStr ? new \DateTime($dateStr) : null;
                if ($date && $date < new \DateTime()) {
                    $errors[] = "La date doit être dans le futur.";
                }
            } catch (\Exception $e) {
                $errors[] = "Format de date invalide.";
            }

            if (empty($errors)) {
                $interview = new Interview();
                $interview->setApplication($jobApplication);
                $interview->setScheduledAt($date);
                $interview->setNotes($notes);
                $interview->setMeetingLink($meetingLink);
                $interview->setStatus('Prévue');

                $entityManager->persist($interview);
                $entityManager->flush();

                $this->addFlash('success', 'Entretien planifié avec succès.');
                return $this->redirectToRoute('app_interview_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->render('interview/new.html.twig', [
            'job_application' => $jobApplication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_interview_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function edit(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($interview->getApplication()->getJobOffre()->getUser() !== $user) {
            $this->addFlash('danger', 'Accès refusé.');
            return $this->redirectToRoute('app_interview_index');
        }

        if ($request->isMethod('POST')) {
            $dateStr = $request->request->get('scheduledAt');
            $notes = $request->request->get('notes');
            $meetingLink = $request->request->get('meetingLink');
            $status = $request->request->get('status');

            // Validation manuelle
            $errors = [];
            if (!$dateStr) $errors[] = "La date est obligatoire.";
            if (strlen($notes) < 10) $errors[] = "Les notes doivent faire au moins 10 caractères.";
            if (!$meetingLink) $meetingLink = "Entretien Vidéo Interne";

            $date = null;
            try {
                $date = $dateStr ? new \DateTime($dateStr) : null;
            } catch (\Exception $e) {
                $errors[] = "Format de date invalide.";
            }

            if (empty($errors)) {
                $interview->setScheduledAt($date);
                $interview->setNotes($notes);
                $interview->setMeetingLink($meetingLink);
                $interview->setStatus($status);

                $entityManager->flush();

                $this->addFlash('success', 'Entretien mis à jour avec succès.');
                return $this->redirectToRoute('app_interview_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        return $this->render('interview/edit.html.twig', [
            'interview' => $interview,
            'job_application' => $interview->getApplication(),
        ]);
    }

    #[Route('/{id}', name: 'app_interview_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function delete(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        $this->assertIsOwner($interview);

        if ($this->isCsrfTokenValid('delete' . $interview->getId(), $request->request->get('_token'))) {
            $entityManager->remove($interview);
            $entityManager->flush();
            $this->addFlash('success', 'Entretien supprimé.');
        }

        return $this->redirectToRoute('app_interview_index');
    }

    #[Route('/{id}/confirm', name: 'app_interview_confirm', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function confirm(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($interview->getApplication()->getCandidat() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $interview->setStatus('Confirmée');
        $entityManager->flush();

        $this->addFlash('success', 'Entretien confirmé.');
        return $this->redirectToRoute('app_interview_index');
    }

    #[Route('/', name: 'app_interview_index')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $interviews = $this->interviewService->getAllInterviews($user);

        return $this->render('interview/index.html.twig', [
            'interviews' => $interviews,
        ]);
    }

    #[Route('/{id}/video', name: 'app_interview_video', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function video(Interview $interview): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $isRecruiter = $interview->getApplication()->getJobOffre()->getUser() === $user;
        $isCandidate = $interview->getApplication()->getCandidat() === $user;

        if (!$isRecruiter && !$isCandidate) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cet entretien.");
        }

        // Generate a unique room name
        $roomName = 'Syfonu_Interview_' . md5('syfonu_' . $interview->getId());

        return $this->render('interview/video.html.twig', [
            'interview' => $interview,
            'roomName' => $roomName,
            'userName' => $user->getFirstName() . ' ' . $user->getLastName(),
        ]);
    }

    #[Route('/{id}/show', name: 'app_interview_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function show(Interview $interview): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $isRecruiter = $interview->getApplication()->getJobOffre()->getUser() === $user;
        $isCandidate = $interview->getApplication()->getCandidat() === $user;

        if (!$isRecruiter && !$isCandidate) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cet entretien.");
        }

        return $this->render('interview/show.html.twig', [
            'interview' => $interview,
        ]);
    }

    private function assertIsOwner(Interview $interview): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($interview->getApplication()?->getJobOffre()?->getUser() !== $user) {
            throw $this->createAccessDeniedException("Accés refusé.");
        }
    }
}
