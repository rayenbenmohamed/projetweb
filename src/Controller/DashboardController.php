<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Repository\ContractRepository;
use App\Repository\InterviewRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\JobOffreRepository;
use App\Repository\ForumPostRepository;
use App\Repository\ForumCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_app_dashboard')]
    public function index(
        JobOffreRepository $jobOffreRepo,
        JobApplicationRepository $appRepo,
        InterviewRepository $interviewRepo,
        UserRepository $userRepo,
        ContractRepository $contractRepo,
        ForumPostRepository $postRepo,
        ForumCategoryRepository $catRepo,
        \App\Service\JobOffreAiService $jobOffreAiService
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // ── Stats Recruteur ──────────────────────────────────────────────────
        $myOffresCount = $jobOffreRepo->count(['user' => $user]);
        $receivedCount = $appRepo->countByRecruiter($user);
        $pendingCount  = $appRepo->countByRecruiter($user, null, JobApplication::STATUS_PENDING);
        $acceptedCount = $appRepo->countByRecruiter($user, null, JobApplication::STATUS_ACCEPTED)
                       + $appRepo->countByRecruiter($user, null, JobApplication::STATUS_READY_FOR_CONTRACT);
        $rejectedCount = $appRepo->countByRecruiter($user, null, JobApplication::STATUS_REJECTED);

        $conversionRate = $receivedCount > 0 ? round(($acceptedCount / $receivedCount) * 100) : 0;

        // AI scores
        $scoredApps = $appRepo->findScoredForRecruiter($user);
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

        // Status distribution
        $recentApps = $appRepo->findByRecruiter($user, null, null, 200, 0);
        $statusDistribution = [
            'Nouveaux'  => 0,
            'Tri RH'    => 0,
            'Entretien' => 0,
            'Revue'     => 0,
            'Acceptés'  => 0,
            'Refusés'   => 0,
        ];
        foreach ($recentApps as $a) {
            match (true) {
                $a->getStatus() === JobApplication::STATUS_PENDING
                    => $statusDistribution['Nouveaux']++,
                $a->getStatus() === JobApplication::STATUS_HR_SCREENING
                    => $statusDistribution['Tri RH']++,
                in_array($a->getStatus(), [JobApplication::STATUS_INTERVIEW_SCHEDULED, JobApplication::STATUS_TECHNICAL_TEST])
                    => $statusDistribution['Entretien']++,
                $a->getStatus() === JobApplication::STATUS_FINAL_REVIEW
                    => $statusDistribution['Revue']++,
                in_array($a->getStatus(), [JobApplication::STATUS_ACCEPTED, JobApplication::STATUS_READY_FOR_CONTRACT])
                    => $statusDistribution['Acceptés']++,
                $a->getStatus() === JobApplication::STATUS_REJECTED
                    => $statusDistribution['Refusés']++,
                default => null,
            };
        }

        $statusLabels = array_keys($statusDistribution);
        $statusData   = array_values($statusDistribution);

        // ── Stats Forum (from dhia) ──────────────────────────────────────────
        $countPosts = $postRepo->count([]);
        $countCategories = $catRepo->count([]);
        $latestPosts = $postRepo->findBy([], ['createdAt' => 'DESC'], 5);

        // ── Stats Candidat ───────────────────────────────────────────────────
        $myApplicationsCount = $appRepo->count(['candidat' => $user]);
        $myAcceptedCount     = $appRepo->countForCandidate($user, JobApplication::STATUS_ACCEPTED)
                             + $appRepo->countForCandidate($user, JobApplication::STATUS_READY_FOR_CONTRACT);

        // ── Interviews ───────────────────────────────────────────────────────
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

        $upcomingInterviews = $interviewRepo->createQueryBuilder('i')
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

        // ── Admin stats ──────────────────────────────────────────────────────
        $adminStats = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            $adminStats = [
                'total_users'        => $userRepo->count([]),
                'total_offres'       => $jobOffreRepo->count([]),
                'total_applications' => $appRepo->count([]),
                'total_contracts'    => $contractRepo->count([]),
            ];
        }

        // ── Smart Matcher ────────────────────────────────────────────────────
        $smartMatches = [];
        if ($this->isGranted('ROLE_USER')) {
            $lastApp = $appRepo->findOneBy(['candidat' => $user], ['applyDate' => 'DESC']);
            if ($lastApp) {
                $allOffres = $jobOffreRepo->findAll();
                $profileText = ($lastApp->getCoverLetter() ?? '') . ' ' . ($lastApp->getCvPath() ?? '');
                $smartMatches = $jobOffreAiService->findTopMatches($profileText, $allOffres);
            }
        }

        return $this->render('dashboard/front_index_fixed.html.twig', [
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
            // Forum
            'count_posts'             => $countPosts,
            'count_categories'        => $countCategories,
            'latest_posts'            => $latestPosts,
            // Candidat
            'count_my_apps'           => $myApplicationsCount,
            'count_my_accepted'       => $myAcceptedCount,
            'smart_matches'           => $smartMatches,
            // Autres
            'latest_jobs'             => $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 5),
            'count_users'             => $userRepo->count([]),
        ]);
    }

    #[Route('/app/smart-matching', name: 'app_app_smart_matching')]
    public function smartMatching(
        JobOffreRepository $jobOffreRepo,
        JobApplicationRepository $appRepo,
        \App\Service\JobOffreAiService $jobOffreAiService,
        HttpClientInterface $httpClient
    ): Response {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $lastApp = $appRepo->findOneBy(['candidat' => $user], ['applyDate' => 'DESC']);
        if (!$lastApp || !$lastApp->getCvPath()) {
            $this->addFlash('warning', "Vous n'avez pas encore téléversé de CV. Postulez à une offre pour commencer l'analyse !");
            return $this->redirectToRoute('app_app_dashboard');
        }

        $cvText = "Profil candidat : " . ($lastApp->getCoverLetter() ?? '');
        try {
            $response = $httpClient->request('GET', $lastApp->getCvPath());
            if ($response->getStatusCode() === 200) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseContent($response->getContent());
                $cvText = $pdf->getText();
            }
        } catch (\Exception $e) {
        }

        $allOffres = $jobOffreRepo->findBy([], ['createdAt' => 'DESC'], 15);
        $recommendations = $jobOffreAiService->getAiRecommendations($cvText, $allOffres);

        $finalResults = [];
        foreach ($recommendations as $rec) {
            $offre = $jobOffreRepo->find($rec['id'] ?? 0);
            if ($offre) {
                $finalResults[] = [
                    'offre' => $offre,
                    'score' => $rec['score'] ?? 0,
                    'reason' => $rec['reason'] ?? 'Compatibilité détectée par l\'IA.'
                ];
            }
        }

        return $this->render('dashboard/smart_matching.html.twig', [
            'recommendations' => $finalResults,
        ]);
    }
}
