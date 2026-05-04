<?php

namespace App\Controller;

use App\Repository\JobApplicationRepository;
use App\Repository\JobOffreRepository;
use App\Service\CVAnalyzerService;
use App\Service\CVParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate')]
class RecommendationController extends AbstractController
{
    #[Route('/recommendations', name: 'app_candidate_recommendations')]
    #[IsGranted('ROLE_USER')]
    public function index(
        JobApplicationRepository $jobAppRepo,
        JobOffreRepository $jobOffreRepo,
        CVParserService $cvParser,
        CVAnalyzerService $cvAnalyzer
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // 1. Trouver le CV le plus récent
        $latestApp = $jobAppRepo->findOneBy(
            ['candidat' => $user],
            ['applyDate' => 'DESC']
        );

        if (!$latestApp || !$latestApp->getCvPath()) {
            return $this->render('candidate/recommendations.html.twig', [
                'error' => 'Aucun CV trouvé. Veuillez postuler à une offre pour que l\'IA puisse analyser votre profil.',
                'recommendations' => []
            ]);
        }

        // 2. Extraire le texte du CV (ou utiliser un cache si on veut optimiser)
        $cvText = $cvParser->extractText($latestApp->getCvPath());
        
        if (str_starts_with($cvText, 'Erreur')) {
            return $this->render('candidate/recommendations.html.twig', [
                'error' => 'Impossible de lire votre CV : ' . $cvText,
                'recommendations' => []
            ]);
        }

        // 3. Récupérer toutes les offres actives (on limite pour éviter de surcharger Gemini)
        $allOffers = $jobOffreRepo->findBy(['status' => 'PUBLISHED'], ['publishedAt' => 'DESC'], 30);

        // 4. Obtenir les recommandations
        $aiResults = $cvAnalyzer->recommendTopJobs($cvText, $allOffers);

        if (!$aiResults || isset($aiResults['error'])) {
            return $this->render('candidate/recommendations.html.twig', [
                'error' => 'L\'IA rencontre une difficulté technique pour analyser les offres.',
                'recommendations' => []
            ]);
        }

        // 5. Récupérer les entités JobOffre réelles pour l'affichage
        $recommendedJobs = [];
        foreach ($aiResults as $res) {
            $job = $jobOffreRepo->find($res['id']);
            if ($job) {
                $recommendedJobs[] = [
                    'job' => $job,
                    'score' => $res['score'],
                    'why' => $res['why']
                ];
            }
        }

        return $this->render('candidate/recommendations.html.twig', [
            'recommendations' => $recommendedJobs,
            'user_cv' => $latestApp->getCvPath()
        ]);
    }
}
