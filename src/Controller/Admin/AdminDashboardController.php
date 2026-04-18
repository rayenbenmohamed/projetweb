<?php

namespace App\Controller\Admin;

use App\Repository\JobApplicationRepository;
use App\Repository\JobOffreRepository;
use App\Repository\UserRepository;
use App\Repository\ContractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        JobOffreRepository $jobOffreRepo,
        JobApplicationRepository $appRepo,
        UserRepository $userRepo,
        ContractRepository $contractRepo,
    ): Response {
        return $this->render('admin/dashboard/index.html.twig', [
            'count_users' => $userRepo->count([]),
            'count_offres' => $jobOffreRepo->count([]),
            'count_applications' => $appRepo->count([]),
            'count_contracts' => $contractRepo->count([]),
            'count_pending_recruiters' => $userRepo->countPendingRecruiters(),
            'latest_jobs' => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
