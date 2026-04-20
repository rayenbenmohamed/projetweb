<?php

namespace App\Controller\Admin;

use App\Repository\JobApplicationRepository;
use App\Repository\JobOffreRepository;
use App\Repository\UserRepository;
use App\Repository\ContractRepository;
use Doctrine\DBAL\Connection;
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
        Connection $connection,
    ): Response {
        $monthly = $this->buildMonthlyEvolutionData($connection);
        $roles = $this->buildUserRoleData($connection);
        $contractStatus = $this->buildContractStatusData($connection);

        return $this->render('admin/dashboard/index.html.twig', [
            'count_users' => $userRepo->count([]),
            'count_offres' => $jobOffreRepo->count([]),
            'count_applications' => $appRepo->count([]),
            'count_contracts' => $contractRepo->count([]),
            'count_pending_recruiters' => $userRepo->countPendingRecruiters(),
            'latest_jobs' => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'admin_chart_data' => [
                'monthly' => $monthly,
                'roles' => $roles,
                'contract_status' => $contractStatus,
            ],
        ]);
    }

    /**
     * @return array{labels: string[], offres: int[], candidatures: int[], contrats: int[]}
     */
    private function buildMonthlyEvolutionData(Connection $connection): array
    {
        $labels = [];
        $keys = [];
        $start = (new \DateTimeImmutable('first day of this month'))->modify('-5 months');
        for ($i = 0; $i < 6; $i++) {
            $month = $start->modify('+' . $i . ' months');
            $keys[] = $month->format('Y-m');
            $labels[] = $month->format('M Y');
        }

        $offresMap = [];
        foreach ($connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c
             FROM job_offre
             WHERE created_at >= :fromDate
             GROUP BY ym",
            ['fromDate' => $start->format('Y-m-d 00:00:00')]
        ) as $row) {
            $offresMap[(string) $row['ym']] = (int) $row['c'];
        }

        $appsMap = [];
        foreach ($connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(apply_date, '%Y-%m') AS ym, COUNT(*) AS c
             FROM job_application
             WHERE apply_date >= :fromDate
             GROUP BY ym",
            ['fromDate' => $start->format('Y-m-d 00:00:00')]
        ) as $row) {
            $appsMap[(string) $row['ym']] = (int) $row['c'];
        }

        $contractsMap = [];
        foreach ($connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(start_date, '%Y-%m') AS ym, COUNT(*) AS c
             FROM contract
             WHERE start_date >= :fromDate
             GROUP BY ym",
            ['fromDate' => $start->format('Y-m-d')]
        ) as $row) {
            $contractsMap[(string) $row['ym']] = (int) $row['c'];
        }

        $offres = [];
        $candidatures = [];
        $contrats = [];
        foreach ($keys as $key) {
            $offres[] = $offresMap[$key] ?? 0;
            $candidatures[] = $appsMap[$key] ?? 0;
            $contrats[] = $contractsMap[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'offres' => $offres,
            'candidatures' => $candidatures,
            'contrats' => $contrats,
        ];
    }

    /**
     * @return array{labels: string[], values: int[]}
     */
    private function buildUserRoleData(Connection $connection): array
    {
        $rows = $connection->fetchAllAssociative(
            "SELECT role, COUNT(*) AS c
             FROM `user`
             GROUP BY role
             ORDER BY c DESC"
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = match ((string) $row['role']) {
                'ROLE_ADMIN' => 'Admins',
                'ROLE_RECRUTEUR' => 'Recruteurs',
                'ROLE_CANDIDAT' => 'Candidats',
                default => (string) $row['role'],
            };
            $values[] = (int) $row['c'];
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: string[], values: int[]}
     */
    private function buildContractStatusData(Connection $connection): array
    {
        $rows = $connection->fetchAllAssociative(
            "SELECT status, COUNT(*) AS c
             FROM contract
             GROUP BY status
             ORDER BY c DESC"
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($row['status'] ?: 'Non défini');
            $values[] = (int) $row['c'];
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
