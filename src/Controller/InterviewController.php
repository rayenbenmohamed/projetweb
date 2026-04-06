<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Service\InterviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\JobApplication;

#[Route('/interview')]
class InterviewController extends AbstractController
{
    private InterviewService $interviewService;

    public function __construct(InterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    #[Route('/new/{applicationId}', name: 'app_interview_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $applicationId, EntityManagerInterface $entityManager): Response
    {
        $jobApplication = $entityManager->getRepository(JobApplication::class)->find($applicationId);
        
        if (!$jobApplication) {
            throw $this->createNotFoundException('Candidature non trouvée.');
        }

        $interview = new Interview();
        $interview->setApplication($jobApplication);

        if ($request->isMethod('POST')) {
            $scheduledAt = new \DateTime($request->request->get('scheduledAt'));
            $interview->setScheduledAt($scheduledAt);
            $interview->setMeetingLink($request->request->get('meetingLink'));
            $interview->setNotes($request->request->get('notes'));
            $interview->setStatus('Prévue');

            $entityManager->persist($interview);
            $entityManager->flush();

            return $this->redirectToRoute('app_job_application_index');
        }

        return $this->render('interview/new.html.twig', [
            'interview' => $interview,
            'job_application' => $jobApplication
        ]);
    }

    #[Route('/{id}/confirm', name: 'app_interview_confirm', methods: ['POST'])]
    public function confirm(Request $request, Interview $interview, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('confirm' . $interview->getId(), $request->request->get('_token'))) {
            $interview->setStatus('Confirmée');
            $entityManager->flush();
            $this->addFlash('success', 'Votre présence a été confirmée pour cet entretien.');
        }

        return $this->redirectToRoute('app_interview_index');
    }

    #[Route('/', name: 'app_interview_index')]
    public function index(\App\Repository\UserRepository $userRepository): Response
    {
        $user = $this->getUser() ?: $userRepository->find(1);
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $interviews = $this->interviewService->getAllInterviews($user);

        return $this->render('interview/index.html.twig', [
            'interviews' => $interviews,
        ]);
    }

    #[Route('/{id}', name: 'app_interview_show')]
    public function show(Interview $interview): Response
    {
        return $this->render('interview/show.html.twig', [
            'interview' => $interview,
        ]);
    }
}
