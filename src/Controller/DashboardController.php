<?php

namespace App\Controller;

use App\Repository\JobOffreRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\UserRepository;
use App\Repository\ContractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(JobOffreRepository $jobOffreRepo, JobApplicationRepository $appRepo, UserRepository $userRepo, ContractRepository $contractRepo): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'count_users' => $userRepo->count([]),
            'count_offres' => $jobOffreRepo->count([]),
            'count_applications' => $appRepo->count([]),
            'count_contracts' => $contractRepo->count([]),
            'latest_jobs' => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
