<?php

namespace App\Controller;

use App\Entity\Interview;
use App\Service\InterviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/interview')]
class InterviewController extends AbstractController
{
    private InterviewService $interviewService;

    public function __construct(InterviewService $interviewService)
    {
        $this->interviewService = $interviewService;
    }

    #[Route('/', name: 'app_interview_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $interviews = $this->interviewService->getInterviewHistory($user);

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
