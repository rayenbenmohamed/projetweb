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
        UserRepository $userRepo,
    ): Response {
        $user = $this->getUser();
        $myApplications = 0;
        if ($user && $this->isGranted('ROLE_CANDIDAT')) {
            $myApplications = $appRepo->count(['candidat' => $user]);
        }

        return $this->render('dashboard/front_index.html.twig', [
            'count_offres' => $jobOffreRepo->count([]),
            'count_applications' => $this->isGranted('ROLE_RECRUTEUR')
                ? $appRepo->count([])
                : $myApplications,
            'latest_jobs' => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
