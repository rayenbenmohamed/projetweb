<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Entity\JobOffre;
use App\Service\CVAnalyzerService;
use App\Repository\JobApplicationRepository;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/job/application')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class JobApplicationController extends AbstractController
{
    /** Analyse IA d'une candidature */
    #[Route('/{id}/analyze', name: 'app_job_application_analyze', methods: ['POST'])]
    public function analyze(
        JobApplication $jobApplication, 
        CVAnalyzerService $cvAnalyzer, 
        EntityManagerInterface $entityManager
    ): Response {
        $this->assertIsOfferOwner($jobApplication);

        $jobDescription = $jobApplication->getJobOffre()?->getDescription() ?? '';
        $cvPath = $jobApplication->getCvPath();

        if (!$cvPath) {
            $this->addFlash('danger', "Aucun CV n'est associé à cette candidature.");
            return $this->redirectToRoute('app_job_application_index');
        }

        // Analyse visuelle directe du document (PDF/Image) via Gemini
        $analysis = $cvAnalyzer->analyzeDocument($jobDescription, $cvPath);

        if (isset($analysis['error'])) {
            $this->addFlash('danger', "Erreur lors de l'analyse : " . $analysis['error']);
        } else {
            $jobApplication->setAiScore($analysis['score'] ?? 0);
            $jobApplication->setAiAnalysis(json_encode($analysis));
            $jobApplication->setAiAnalyzedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', "Analyse IA terminée avec succès !");
        }

        return $this->redirectToRoute('app_job_application_index');
    }

    /** Exportation des candidatures en CSV */
    #[Route('/export/csv', name: 'app_job_application_export_csv', methods: ['GET'])]
    public function exportCsv(JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();
        $applications = $jobApplicationRepository->findByRecruiter($user, null, null, 9999, 0);

        $csvData = [];
        $csvData[] = ['ID', 'Candidat', 'Email', 'Offre', 'Date', 'Statut', 'Score IA'];

        foreach ($applications as $app) {
            $csvData[] = [
                $app->getId(),
                $app->getCandidat()?->getFirstName() . ' ' . $app->getCandidat()?->getLastName(),
                $app->getCandidat()?->getEmail(),
                $app->getJobOffre()?->getTitle(),
                $app->getApplyDate()?->format('d/m/Y'),
                $app->getStatus(),
                $app->getAiScore() ?? 'N/A'
            ];
        }

        $fp = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $response = new Response(stream_get_contents($fp));
        fclose($fp);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="candidatures_export_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /** Analyse IA manuelle (via texte collé) */
    #[Route('/{id}/analyze-manual', name: 'app_job_application_analyze_manual', methods: ['POST'])]
    public function analyzeManual(
        Request $request,
        JobApplication $jobApplication, 
        CVAnalyzerService $cvAnalyzer, 
        EntityManagerInterface $entityManager
    ): Response {
        $this->assertIsOfferOwner($jobApplication);

        $cvText = $request->request->get('cv_text');
        $jobDescription = $jobApplication->getJobOffre()?->getDescription() ?? '';

        if (!$cvText) {
            $this->addFlash('danger', "Veuillez saisir le texte du CV.");
            return $this->redirectToRoute('app_job_application_show', ['id' => $jobApplication->getId()]);
        }

        $analysis = $cvAnalyzer->analyze($jobDescription, $cvText);

        if (isset($analysis['error'])) {
            $this->addFlash('danger', "Erreur lors de l'analyse manuelle : " . $analysis['error']);
        } else {
            $jobApplication->setAiScore($analysis['score'] ?? 0);
            $jobApplication->setAiAnalysis(json_encode($analysis));
            $jobApplication->setAiAnalyzedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', "Analyse manuelle terminée avec succès !");
        }

        return $this->redirectToRoute('app_job_application_show', ['id' => $jobApplication->getId()]);
    }
    /**
     * Liste des candidatures :
     * - Propriétaire de l'offre : voit les candidatures de SES offres
     * - Sinon : voit ses propres candidatures (en tant que candidat)
     */
    #[Route('/', name: 'app_job_application_index', methods: ['GET'])]
    public function index(JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();

        // Candidatures où l'utilisateur EST le propriétaire de l'offre
        $asRecruiter = $jobApplicationRepository->findByRecruiter($user);

        // Candidatures où l'utilisateur a postulé
        $asCandidat = $jobApplicationRepository->findBy(['candidat' => $user]);

        // Grouper les candidatures reçues (en tant que recruteur) par offre
        $applicationsByOffer = [];
        foreach ($asRecruiter as $app) {
            $offerTitle = $app->getJobOffre()?->getTitle() ?? 'Offre inconnue';
            $applicationsByOffer[$offerTitle][] = $app;
        }

        return $this->render('job_application/index.html.twig', [
            'applications_by_offer' => $applicationsByOffer,
            'my_applications'       => $asCandidat,
        ]);
    }

    /** Postuler à une offre */
    #[Route('/new/{id}', name: 'app_job_application_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        JobOffre $jobOffre,
        EntityManagerInterface $entityManager,
        CloudinaryService $cloudinaryService,
        JobApplicationRepository $jobApplicationRepository
    ): Response {
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà postulé à cette offre
        $existingApplication = $jobApplicationRepository->findOneBy([
            'jobOffre' => $jobOffre,
            'candidat' => $user
        ]);

        if ($existingApplication) {
            $this->addFlash('warning', 'Vous avez déjà postulé à cette offre.');
            return $this->redirectToRoute('app_job_offre_show', ['id' => $jobOffre->getId()]);
        }

        $jobApplication = new JobApplication();

        if ($request->isMethod('POST')) {
            $jobApplication->setJobOffre($jobOffre);
            $jobApplication->setCandidat($this->getUser());
            $jobApplication->setCoverLetter($request->request->get('cover_letter'));

            $cvFile = $request->files->get('cv_file');
            if ($cvFile) {
                $cvUrl = $cloudinaryService->uploadFile($cvFile, 'cvs');
                $jobApplication->setCvPath($cvUrl);
            } else {
                $jobApplication->setCvPath($request->request->get('cv_path'));
            }

            $entityManager->persist($jobApplication);
            $entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée avec succès !');

            // --- WhatsApp Redirection Logic ---
            $entreprise = $jobOffre->getEntreprise();
            
            // Fallback : Si l'offre n'est pas liée directement, on cherche l'entreprise du créateur de l'offre
            if (!$entreprise && $jobOffre->getUser()) {
                $entreprise = $jobOffre->getUser()->getEntreprise();
            }

            if ($entreprise && $entreprise->getPhone()) {
                // Nettoyer le numéro (garder seulement les chiffres)
                $phoneNumber = preg_replace('/[^0-9]/', '', $entreprise->getPhone());
                
                if (!empty($phoneNumber)) {
                    /** @var \App\Entity\User $user */
                    $user = $this->getUser();
                    $candidateName = $user->getFirstName() . ' ' . $user->getLastName();
                    $jobTitle = $jobOffre->getTitle();
                    
                    $message = "Bonjour, je suis $candidateName. Je viens de postuler à votre offre d'emploi \"$jobTitle\" sur la plateforme SyfonuRH. Dans l'attente de votre retour, je vous souhaite une excellente journée.";
                    $whatsappUrl = "https://wa.me/$phoneNumber?text=" . urlencode($message);
                    
                    return $this->redirect($whatsappUrl);
                }
            }

            return $this->redirectToRoute('app_job_offre_index');
        }

        return $this->render('job_application/new.html.twig', ['job_offre' => $jobOffre]);
    }

    /** Voir une candidature */
    #[Route('/{id}', name: 'app_job_application_show', methods: ['GET'])]
    public function show(JobApplication $jobApplication): Response
    {
        $this->assertCanAccess($jobApplication);

        return $this->render('job_application/show.html.twig', [
            'job_application' => $jobApplication,
        ]);
    }

    /** Modifier une candidature (propriétaire de l'offre seulement) */
    #[Route('/{id}/edit', name: 'app_job_application_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        $this->assertIsOfferOwner($jobApplication);

        if ($request->isMethod('POST')) {
            $jobApplication->setCoverLetter($request->request->get('cover_letter'));
            $jobApplication->setStatus($request->request->get('status'));
            $entityManager->flush();

            $this->addFlash('success', 'Candidature mise à jour.');
            return $this->redirectToRoute('app_job_application_index');
        }

        return $this->render('job_application/edit.html.twig', ['job_application' => $jobApplication]);
    }

    #[Route('/{id}/delete', name: 'app_job_application_delete', methods: ['POST'])]
    public function delete(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        // 1. Vérification des permissions
        $this->assertCanDelete($jobApplication);

        // 2. Vérification du jeton CSRF
        $tokenId = 'delete' . $jobApplication->getId();
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid($tokenId, $token)) {
            $entityManager->remove($jobApplication);
            $entityManager->flush();
            $this->addFlash('success', 'La candidature a été supprimée définitivement.');
        } else {
            $this->addFlash('danger', 'Erreur de sécurité (jeton invalide). Veuillez actualiser la page et réessayer.');
        }

        // 3. Redirection intelligente vers la page d'origine (ou l'index par défaut)
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, $request->getHost())) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_job_application_index');
    }

    /** Changer le statut (propriétaire de l'offre seulement) */
    #[Route('/{id}/status', name: 'app_job_application_status', methods: ['POST'])]
    public function updateStatus(Request $request, JobApplication $jobApplication, EntityManagerInterface $entityManager): Response
    {
        $this->assertIsOfferOwner($jobApplication);

        $status = $request->request->get('status');
        $jobApplication->setStatus($status);
        $entityManager->flush();

        if ($status === 'ACCEPTED') {
            // Check if an interview already exists
            $existingInterview = $jobApplication->getInterviews()->first();
            if ($existingInterview) {
                return $this->redirectToRoute('app_interview_edit', ['id' => $existingInterview->getId()]);
            }
            return $this->redirectToRoute('app_interview_new', ['jobApplication' => $jobApplication->getId()]);
        }

        return $this->redirectToRoute('app_job_application_index');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * L'utilisateur peut voir la candidature s'il en est l'auteur
     * OU s'il est le propriétaire de l'offre liée.
     */
    private function assertCanAccess(JobApplication $app): void
    {
        $user = $this->getUser();
        if (!$user) throw $this->createAccessDeniedException();

        $isCandidat   = $app->getCandidat() && $app->getCandidat()->getId() === $user->getId();
        $isOfferOwner = $app->getJobOffre() && $app->getJobOffre()->getUser() && $app->getJobOffre()->getUser()->getId() === $user->getId();

        if (!$isCandidat && !$isOfferOwner) {
            $this->addFlash('danger', "Accès refusé.");
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * Seul le propriétaire de l'offre peut gérer la candidature.
     */
    private function assertIsOfferOwner(JobApplication $app): void
    {
        $user = $this->getUser();
        if (!$user) throw $this->createAccessDeniedException();

        $isOfferOwner = $app->getJobOffre() && $app->getJobOffre()->getUser() && $app->getJobOffre()->getUser()->getId() === $user->getId();

        if (!$isOfferOwner) {
            $this->addFlash('danger', "Action réservée au recruteur de cette offre.");
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * L'utilisateur peut supprimer s'il est :
     * 1. Le candidat (auteur)
     * 2. Le propriétaire de l'offre (recruteur)
     */
    private function assertCanDelete(JobApplication $app): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $appUser = $app->getCandidat();
        $offerOwner = $app->getJobOffre() ? $app->getJobOffre()->getUser() : null;

        $isCandidat = $appUser && $appUser->getId() === $user->getId();
        $isOfferOwner = $offerOwner && $offerOwner->getId() === $user->getId();

        if (!$isCandidat && !$isOfferOwner) {
            $this->addFlash('danger', "Action refusée. Vous ne pouvez supprimer que vos propres candidatures ou celles reçues sur vos offres.");
            throw $this->createAccessDeniedException();
        }
    }
}
