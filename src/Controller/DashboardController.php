<?php

namespace App\Controller;

use App\Repository\JobOffreRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/app', name: 'app_app_dashboard')]
    public function index(
        JobOffreRepository $jobOffreRepo,
        JobApplicationRepository $appRepo,
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Stats for the user as a recruiter (his offers and applications received)
        $myOffresCount = $jobOffreRepo->count(['user' => $user]);
        $receivedApplicationsCount = $appRepo->countByRecruiter($user);

        // Stats for the user as a candidate (his sent applications)
        $myApplicationsCount = $appRepo->count(['candidat' => $user]);

        return $this->render('dashboard/front_index.html.twig', [
            'count_my_offres' => $myOffresCount,
            'count_received_apps' => $receivedApplicationsCount,
            'count_my_apps' => $myApplicationsCount,
            'latest_jobs' => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
