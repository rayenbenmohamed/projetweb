<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Repository\ContractRepository;
use App\Repository\InterviewRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\JobOffreRepository;
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
        InterviewRepository $interviewRepo,
        UserRepository $userRepo,
        ContractRepository $contractRepo,
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // ── Stats Recruteur ──────────────────────────────────────────────────
        $myOffresCount        = $jobOffreRepo->count(['user' => $user]);
        $receivedApps         = $appRepo->findByRecruiter($user, null, null, 9999, 0);
        $receivedCount        = count($receivedApps);
        $pendingCount         = count(array_filter($receivedApps, fn($a) => $a->getStatus() === JobApplication::STATUS_PENDING));
        $acceptedCount        = count(array_filter($receivedApps, fn($a) => in_array($a->getStatus(), [JobApplication::STATUS_ACCEPTED, JobApplication::STATUS_READY_FOR_CONTRACT])));
        $rejectedCount        = count(array_filter($receivedApps, fn($a) => $a->getStatus() === JobApplication::STATUS_REJECTED));
        $scoredApps = array_values(array_filter($receivedApps, fn($a) => $a->getAiScore() !== null));
        $avgScore   = null;
        $topName    = null;
        $topScore   = null;
        if (count($scoredApps) > 0) {
            usort($scoredApps, fn($a, $b) => (int)$b->getAiScore() <=> (int)$a->getAiScore());
            $scores   = array_map(fn($a) => (int)$a->getAiScore(), $scoredApps);
            $avgScore = round(array_sum($scores) / count($scores));
            $best     = $scoredApps[0];
            $topName  = $best->getCandidat()?->getFirstName() . ' ' . $best->getCandidat()?->getLastName();
            $topScore = $best->getAiScore();
        }

        $conversionRate = $receivedCount > 0 ? round(($acceptedCount / $receivedCount) * 100) : 0;

        // ── Stats Candidat ────────────────────────────────────────────────────
        $myApplicationsCount  = $appRepo->count(['candidat' => $user]);
        $myAcceptedCount      = count($appRepo->findForCandidate($user, JobApplication::STATUS_ACCEPTED, null, 9999, 0))
                              + count($appRepo->findForCandidate($user, JobApplication::STATUS_READY_FOR_CONTRACT, null, 9999, 0));

        // ── Données pour les Graphiques (Chart.js) ───────────────────────────
        $statusDistribution = [
            'Nouveaux'    => count(array_filter($receivedApps, fn($a) => $a->getStatus() === JobApplication::STATUS_PENDING)),
            'Tri RH'      => count(array_filter($receivedApps, fn($a) => $a->getStatus() === JobApplication::STATUS_HR_SCREENING)),
            'Entretien'   => count(array_filter($receivedApps, fn($a) => in_array($a->getStatus(), [JobApplication::STATUS_INTERVIEW_SCHEDULED, JobApplication::STATUS_TECHNICAL_TEST]))),
            'Revue'       => count(array_filter($receivedApps, fn($a) => $a->getStatus() === JobApplication::STATUS_FINAL_REVIEW)),
            'Acceptés'    => $acceptedCount,
            'Refusés'     => $rejectedCount,
        ];
        
        $statusLabels = array_keys($statusDistribution);
        $statusData   = array_values($statusDistribution);

        // Moyennes des entretiens réalisés
        $completedInterviews = $interviewRepo->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->join('a.jobOffre', 'jo')
            ->where('jo.user = :user')
            ->andWhere('i.status IN (:done)')
            ->setParameter('user', $user)
            ->setParameter('done', ['Réalisée', 'Archivée'])
            ->getQuery()
            ->getResult();

        $avgRatings = ['tech' => 0, 'comm' => 0, 'mot' => 0];
        $ciCount = count($completedInterviews);
        if ($ciCount > 0) {
            $avgRatings['tech'] = array_sum(array_map(fn($i) => $i->getTechnicalRating() ?? 0, $completedInterviews)) / $ciCount;
            $avgRatings['comm'] = array_sum(array_map(fn($i) => $i->getCommunicationRating() ?? 0, $completedInterviews)) / $ciCount;
            $avgRatings['mot']  = array_sum(array_map(fn($i) => $i->getMotivationRating() ?? 0, $completedInterviews)) / $ciCount;
        }

        // ── Prochains entretiens (recruteur) ──────────────────────────────────
        $upcomingInterviews   = $interviewRepo->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->join('a.jobOffre', 'jo')
            ->where('jo.user = :user')
            ->andWhere('i.scheduledAt >= :now')
            ->andWhere('i.status NOT IN (:done)')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->setParameter('done', ['Réalisée', 'Archivée', 'Annulée'])
            ->orderBy('i.scheduledAt', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // ── Stats Globales (Admin uniquement) ────────────────────────────────
        $adminStats = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            $adminStats = [
                'total_users'        => $userRepo->count([]),
                'total_offres'       => $jobOffreRepo->count([]),
                'total_applications' => $appRepo->count([]),
                'total_contracts'    => $contractRepo->count([]),
            ];
        }

        return $this->render('dashboard/front_index.html.twig', [
            // Recruteur
            'count_my_offres'         => $myOffresCount,
            'count_received_apps'     => $receivedCount,
            'count_pending'           => $pendingCount,
            'count_accepted'          => $acceptedCount,
            'count_rejected'          => $rejectedCount,
            'avg_score'               => $avgScore,
            'top_name'                => $topName,
            'top_score'               => $topScore,
            'conversion_rate'         => $conversionRate,
            'status_labels'           => $statusLabels,
            'status_data'             => $statusData,
            'avg_ratings'             => $avgRatings,
            'upcoming_interviews'     => $upcomingInterviews,
            'admin_stats'             => $adminStats,
            // Candidat
            'count_my_apps'           => $myApplicationsCount,
            'count_my_accepted'       => $myAcceptedCount,
            // Autres
            'latest_jobs'             => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
