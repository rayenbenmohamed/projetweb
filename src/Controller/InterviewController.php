<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Entity\JobApplication;
use App\Service\InterviewService;
use App\Service\NotificationService;
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
    private NotificationService $notificationService;

    public function __construct(InterviewService $interviewService, NotificationService $notificationService)
    {
        $this->interviewService    = $interviewService;
        $this->notificationService = $notificationService;
    }

    #[Route('/new/{jobApplication}', name: 'app_interview_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

                // ── Notification au candidat ─────────────────────────────
                $candidate  = $jobApplication->getCandidat();
                $offreTitle = $jobApplication->getJobOffre()?->getTitle() ?? 'votre candidature';
                $dateFormatted = $date ? $date->format('d/m/Y \u00e0 H:i') : '';
                if ($candidate) {
                    $this->notificationService->addNotification(
                        $candidate,
                        '📅 Un entretien a été planifié pour "' . $offreTitle . '". Date : ' . $dateFormatted
                    );
                }

                $this->addFlash('success', 'Entretien planifié avec succès.');
                return $this->redirectToRoute('app_interview_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('danger', $error);
            }
        }

        $existingInterviews = $this->interviewService->getAllInterviews($user);

        return $this->render('interview/new.html.twig', [
            'job_application' => $jobApplication,
            'existingInterviews' => $existingInterviews,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_interview_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

        $existingInterviews = $this->interviewService->getAllInterviews($user);

        return $this->render('interview/edit.html.twig', [
            'interview' => $interview,
            'job_application' => $interview->getApplication(),
            'existingInterviews' => $existingInterviews,
        ]);
    }

    #[Route('/{id}', name: 'app_interview_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

        $allInterviews = $this->interviewService->getAllInterviews($user);
        
        $upcoming = [];
        $completed = [];

        foreach ($allInterviews as $interview) {
            $isCompleted = ($interview->getStatus() === 'Réalisée' || $interview->getStatus() === 'Archivée');
            
            if ($isCompleted && $interview->getFinalVerdict()) {
                // Calcul du score moyen
                $totalPoints = $interview->getTechnicalRating() + $interview->getCommunicationRating() + $interview->getMotivationRating();
                $averageScore = round(($totalPoints / 15) * 100, 1);
                $interview->averageScore = $averageScore; // Propriété temporaire pour le tri
                $completed[] = $interview;
            } else {
                $upcoming[] = $interview;
            }
        }

        // Tri des complétés par score moyen décroissant
        usort($completed, fn($a, $b) => $b->averageScore <=> $a->averageScore);

        return $this->render('interview/index.html.twig', [
            'upcoming' => $upcoming,
            'completed' => $completed,
        ]);
    }

    #[Route('/{id}/video', name: 'app_interview_video', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

    #[Route('/{id}/evaluate', name: 'app_interview_evaluate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function evaluate(
        Request $request, 
        Interview $interview, 
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($interview->getApplication()->getJobOffre()->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $interview->setTechnicalRating((int) $request->request->get('technicalRating'));
        $interview->setCommunicationRating((int) $request->request->get('communicationRating'));
        $interview->setMotivationRating((int) $request->request->get('motivationRating'));
        $interview->setFinalVerdict($request->request->get('finalVerdict'));
        
        $outcome = $request->request->get('outcome', 'PENDING_DECISION');
        $interview->setOutcome($outcome);
        $interview->setCompletedAt(new \DateTime());
        $interview->setStatus('Réalisée');

        // Optionnel : Mise à jour automatique du statut de la candidature
        $application = $interview->getApplication();
        $candidate = $application->getCandidat();
        $offerTitle = $application->getJobOffre()?->getTitle() ?? 'l\'offre';

        if ($outcome === 'ACCEPTED') {
            $application->setStatus(JobApplication::STATUS_FINAL_REVIEW);
            if ($candidate) {
                $notificationService->addNotification($candidate, "🎯 Bravo ! Votre entretien pour \"$offerTitle\" a été concluant. Vous passez en revue finale.");
            }
        } elseif ($outcome === 'READY_FOR_CONTRACT') {
            $application->setStatus(JobApplication::STATUS_READY_FOR_CONTRACT);
            if ($candidate) {
                $notificationService->addNotification($candidate, "📄 Félicitations ! Votre entretien pour \"$offerTitle\" est validé. Nous préparons votre contrat !");
            }
        } elseif ($outcome === 'REJECTED') {
            $application->setStatus(JobApplication::STATUS_REJECTED);
            if ($candidate) {
                $notificationService->addNotification($candidate, "👋 Votre entretien pour \"$offerTitle\" a été analysé. Malheureusement, nous ne donnerons pas suite à votre candidature.");
            }
        } else {
            if ($candidate) {
                $notificationService->addNotification($candidate, "📅 Votre entretien pour \"$offerTitle\" du " . $interview->getScheduledAt()->format('d/m') . " est terminé. Nous vous ferons part de notre décision prochainement.");
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Évaluation enregistrée avec succès. Résultat : ' . $outcome);
        return $this->redirectToRoute('app_interview_show', ['id' => $interview->getId()]);
    }

    #[Route('/{id}/archive', name: 'app_interview_archive', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function archive(Interview $interview, EntityManagerInterface $entityManager): Response
    {
        $this->assertIsOwner($interview);
        $interview->setStatus('Archivée');
        $entityManager->flush();

        $this->addFlash('info', 'Entretien archivé dans l\'historique.');
        return $this->redirectToRoute('app_interview_index');
    }

    #[Route('/{id}/ai-suggest', name: 'app_interview_ai_suggest', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function aiSuggest(
        Interview $interview, 
        \App\Service\CVAnalyzerService $cvAnalyzer,
        \App\Service\CVParserService $cvParser
    ): \Symfony\Component\HttpFoundation\JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($interview->getApplication()->getJobOffre()->getUser() !== $user) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $jobDesc = $interview->getApplication()->getJobOffre()->getDescription();
        $cvPath = $interview->getApplication()->getCvPath();
        $cvText = $cvPath ? $cvParser->extractText($cvPath) : '';
        $notes = $interview->getNotes() ?? '';

        $suggestion = $cvAnalyzer->suggestEvaluation($jobDesc, $cvText, $notes);

        return $this->json($suggestion);
    }

    #[Route('/{id}/show', name: 'app_interview_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
