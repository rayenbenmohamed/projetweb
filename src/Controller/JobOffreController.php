<?php

namespace App\Controller;

use App\Entity\JobOffre;
use App\Entity\OfferStatus;
use App\Entity\Avantage;
use App\Repository\JobOffreRepository;
use App\Repository\AvantageRepository;
use App\Repository\JobApplicationRepository;
use App\Service\CloudinaryUploader;
use App\Service\JobOffreAiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/job/offre')]
class JobOffreController extends AbstractController
{
    /** Vue publique : toutes les offres, pour tout le monde */
    #[Route('/', name: 'app_job_offre_index', methods: ['GET'])]
    public function index(Request $request, JobOffreRepository $jobOffreRepository, JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();
        $userApplications = [];

        if ($user) {
            $applications = $jobApplicationRepository->findBy(['candidat' => $user]);
            foreach ($applications as $app) {
                $userApplications[$app->getJobOffre()->getId()] = $app;
            }
        }

        $filters = [
            'q'          => $request->query->get('q', ''),
            'type'       => $request->query->get('type', ''),
            'status'     => $request->query->get('status', ''),
            'location'   => $request->query->get('location', ''),
            'salary_min' => $request->query->get('salary_min', ''),
            'salary_max' => $request->query->get('salary_max', ''),
        ];

        return $this->render('job_offre/index.html.twig', [
            'job_offres'        => $jobOffreRepository->search($filters),
            'user_applications' => $userApplications,
            'stats'             => $jobOffreRepository->getStats(),
            'filters'           => $filters,
        ]);
    }

    /** Mes offres : seulement les offres du user connecté */
    #[Route('/mes-offres', name: 'app_job_offre_my', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myOffers(Request $request, JobOffreRepository $jobOffreRepository): Response
    {
        $filters = [
            'q'          => $request->query->get('q', ''),
            'type'       => $request->query->get('type', ''),
            'status'     => $request->query->get('status', ''),
            'location'   => $request->query->get('location', ''),
            'salary_min' => $request->query->get('salary_min', ''),
            'salary_max' => $request->query->get('salary_max', ''),
            'user'       => $this->getUser(),
        ];

        return $this->render('job_offre/my.html.twig', [
            'job_offres' => $jobOffreRepository->search($filters),
            'stats'      => $jobOffreRepository->getStats($this->getUser()),
            'filters'    => $filters,
        ]);
    }

    #[Route('/new', name: 'app_job_offre_new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $entityManager, AvantageRepository $avantageRepository, ValidatorInterface $validator, CloudinaryUploader $uploader): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        // ── LOGIQUE : L'utilisateur DOIT avoir une entreprise pour créer un job ──
        if (!$entreprise) {
            $this->addFlash('info', 'Veuillez enregistrer votre entreprise avant de publier une offre.');
            return $this->redirectToRoute('app_entreprise_new');
        }

        $jobOffre = new JobOffre();
        $avantages = $avantageRepository->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {
            $jobOffre->setTitle((string) $request->request->get('title', ''));
            $jobOffre->setDescription((string) $request->request->get('description', ''));
            $jobOffre->setLocation($request->request->get('location') ?: null);
            $salaryRaw = $request->request->get('salary');
            $jobOffre->setSalary($salaryRaw !== '' && $salaryRaw !== null ? (float) $salaryRaw : null);
            $jobOffre->setEmploymentType((string) $request->request->get('employment_type', ''));
            $jobOffre->setStatus((string) $request->request->get('status', 'PUBLISHED'));
            $jobOffre->setAdvantages($request->request->get('advantages') ?: null);
            $jobOffre->setSalaryNegotiable($request->request->get('is_salary_negotiable') === 'on');
            $jobOffre->setSkills($request->request->get('skills') ?: null);
            
            // On lie automatiquement à l'entreprise du recruteur
            $jobOffre->setEntreprise($entreprise);

            // Dates ... (le reste du code est identique)
            $publishedAtRaw = $request->request->get('published_at');
            $jobOffre->setPublishedAt($publishedAtRaw ? new \DateTime($publishedAtRaw) : new \DateTime());

            $expiresAtRaw = $request->request->get('expires_at');
            if ($expiresAtRaw) {
                $expiresAt = new \DateTime($expiresAtRaw);
                if ($expiresAt <= $jobOffre->getPublishedAt()) {
                    $errors['expires_at'] = "La date d'expiration doit être postérieure à la date de publication.";
                } else {
                    $jobOffre->setExpiresAt($expiresAt);
                }
            }

            // Upload logo Cloudinary
            $logoFile = $request->files->get('company_logo');
            if ($logoFile) {
                try {
                    $uploaded = $uploader->uploadLogo($logoFile);
                    $jobOffre->setCompanyLogo($uploaded['url']);
                    $jobOffre->setCompanyLogoPublicId($uploaded['publicId']);
                } catch (\RuntimeException $e) {
                    $errors['company_logo'] = $e->getMessage();
                }
            }

            // Avantages liés
            $selectedAvantages = $request->request->all('selected_avantages');
            foreach ($selectedAvantages as $avId) {
                $av = $avantageRepository->find($avId);
                if ($av) $jobOffre->addLinkedAvantage($av);
            }

            $jobOffre->setUser($this->getUser());

            // Validation via les contraintes Assert de l'entité
            $violations = $validator->validate($jobOffre);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            // Auto-publish/archive logic based on date
            if ($jobOffre->getStatus() !== 'DRAFT') {
                if ($jobOffre->isExpired()) {
                    $jobOffre->setStatus('ARCHIVED');
                } elseif ($jobOffre->getStatus() === 'ARCHIVED' && !$jobOffre->isExpired()) {
                    $jobOffre->setStatus('PUBLISHED');
                }
            }

            if (empty($errors)) {
                $entityManager->persist($jobOffre);
                $entityManager->flush();
                $this->addFlash('success', 'Offre créée avec succès.');
                return $this->redirectToRoute('app_job_offre_my');
            }
        }

        return $this->render('job_offre/new.html.twig', [
            'job_offre' => $jobOffre,
            'avantages' => $avantages,
            'errors'    => $errors,
        ]);
    }

    /**
     * AI Chat: parse a natural-language query and return job-offer filter criteria.
     */
    #[Route('/ai-chat', name: 'app_job_offre_ai_chat', methods: ['POST'])]
    public function aiChat(Request $request, JobOffreAiService $aiService): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if ($message === '') {
            return new JsonResponse(['error' => 'Message vide.'], 400);
        }

        try {
            $result = $aiService->parseQuery($message);
            return new JsonResponse($result);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/ai-generate-description', name: 'app_job_offre_ai_generate', methods: ['POST'])]
    public function aiGenerateDescription(Request $request, JobOffreAiService $aiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        try {
            $description = $aiService->generateDescription($data);
            return new JsonResponse(['description' => $description]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/translate', name: 'app_job_offre_translate', methods: ['POST'])]
    public function translate(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        $target = $data['target'] ?? 'en';

        if (empty($text)) {
            return new JsonResponse(['error' => 'No text provided'], 400);
        }

        try {
            // Utilisation du miroir stable lingva.ml
            $url = sprintf('https://lingva.ml/api/v1/auto/%s/%s', $target, rawurlencode($text));
            $response = $httpClient->request('GET', $url);
            
            $result = $response->toArray();

            return new JsonResponse([
                'translation' => $result['translation'] ?? null
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'app_job_offre_show', methods: ['GET'])]
    public function show(JobOffre $jobOffre, JobApplicationRepository $jobApplicationRepository): Response
    {
        $user = $this->getUser();
        $userApplication = null;

        if ($user) {
            $userApplication = $jobApplicationRepository->findOneBy([
                'jobOffre' => $jobOffre,
                'candidat' => $user
            ]);
        }

        return $this->render('job_offre/show.html.twig', [
            'job_offre'        => $jobOffre,
            'user_application' => $userApplication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_offre_edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager, AvantageRepository $avantageRepository, ValidatorInterface $validator, CloudinaryUploader $uploader): Response
    {
        $avantages = $avantageRepository->findAll();
        $errors = [];

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        if ($request->isMethod('POST')) {
            $jobOffre->setTitle((string) $request->request->get('title', ''));
            $jobOffre->setDescription((string) $request->request->get('description', ''));
            $jobOffre->setLocation($request->request->get('location') ?: null);
            $salaryRaw = $request->request->get('salary');
            $jobOffre->setSalary($salaryRaw !== '' && $salaryRaw !== null ? (float) $salaryRaw : null);
            $jobOffre->setEmploymentType((string) $request->request->get('employment_type', ''));
            $jobOffre->setStatus((string) $request->request->get('status', 'DRAFT'));
            $jobOffre->setAdvantages($request->request->get('advantages') ?: null);
            $jobOffre->setSalaryNegotiable($request->request->get('is_salary_negotiable') === 'on');
            $jobOffre->setSkills($request->request->get('skills') ?: null);

            // On s'assure que l'entreprise est bien liée (sécurité)
            if ($user->getEntreprise()) {
                $jobOffre->setEntreprise($user->getEntreprise());
            }

            // Dates
            if ($request->request->get('published_at')) {
                $jobOffre->setPublishedAt(new \DateTime($request->request->get('published_at')));
            }
            $expiresAtRaw = $request->request->get('expires_at');
            if ($expiresAtRaw) {
                $expiresAt = new \DateTime($expiresAtRaw);
                if ($expiresAt <= $jobOffre->getPublishedAt()) {
                    $errors['expires_at'] = "La date d'expiration doit être postérieure à la date de publication.";
                } else {
                    $jobOffre->setExpiresAt($expiresAt);
                }
            } else {
                $jobOffre->setExpiresAt(null);
            }

            // Upload logo Cloudinary (remplacement)
            $logoFile = $request->files->get('company_logo');
            if ($logoFile) {
                try {
                    if ($jobOffre->getCompanyLogoPublicId()) {
                        $uploader->deleteLogo($jobOffre->getCompanyLogoPublicId());
                    }
                    $uploaded = $uploader->uploadLogo($logoFile);
                    $jobOffre->setCompanyLogo($uploaded['url']);
                    $jobOffre->setCompanyLogoPublicId($uploaded['publicId']);
                } catch (\RuntimeException $e) {
                    $errors['company_logo'] = $e->getMessage();
                }
            }

            // Supprimer le logo si demandé
            if ($request->request->get('remove_logo') === '1' && !$logoFile) {
                if ($jobOffre->getCompanyLogoPublicId()) {
                    $uploader->deleteLogo($jobOffre->getCompanyLogoPublicId());
                }
                $jobOffre->setCompanyLogo(null);
                $jobOffre->setCompanyLogoPublicId(null);
            }

            // Avantages
            foreach ($jobOffre->getLinkedAvantages()->toArray() as $av) {
                $jobOffre->removeLinkedAvantage($av);
            }
            $selectedAvantages = $request->request->all('selected_avantages');
            foreach ($selectedAvantages as $avId) {
                $av = $avantageRepository->find($avId);
                if ($av) $jobOffre->addLinkedAvantage($av);
            }

            $jobOffre->setUpdatedAt(new \DateTime());

            // Validation via les contraintes Assert de l'entité
            $violations = $validator->validate($jobOffre);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            // Auto-publish/archive logic based on date
            if ($jobOffre->getStatus() !== 'DRAFT') {
                if ($jobOffre->isExpired()) {
                    $jobOffre->setStatus('ARCHIVED');
                } elseif ($jobOffre->getStatus() === 'ARCHIVED' && !$jobOffre->isExpired()) {
                    $jobOffre->setStatus('PUBLISHED');
                }
            }

            if (empty($errors)) {
                $entityManager->flush();
                $this->addFlash('success', 'Offre modifiée avec succès.');
                return $this->redirectToRoute('app_job_offre_my');
            }
        }

        return $this->render('job_offre/edit.html.twig', [
            'job_offre' => $jobOffre,
            'avantages' => $avantages,
            'errors'    => $errors,
        ]);
    }

    #[Route('/{id}/duplicate', name: 'app_job_offre_duplicate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function duplicate(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('duplicate' . $jobOffre->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_job_offre_my');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez dupliquer que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        $copy = new JobOffre();
        $copy->setTitle('[Copie] ' . $jobOffre->getTitle());
        $copy->setDescription($jobOffre->getDescription());
        $copy->setLocation($jobOffre->getLocation());
        $copy->setSalary($jobOffre->getSalary());
        $copy->setEmploymentType($jobOffre->getEmploymentType());
        $copy->setSalaryNegotiable($jobOffre->isSalaryNegotiable());
        $copy->setAdvantages($jobOffre->getAdvantages());
        $copy->setSkills($jobOffre->getSkills());
        // Ne pas copier le logo (public_id lié à l'original)
        $copy->setCompanyLogo($jobOffre->getCompanyLogo());
        $copy->setStatus('DRAFT');
        $copy->setPublishedAt(new \DateTime());
        $copy->setUser($this->getUser());

        $entityManager->persist($copy);
        $entityManager->flush();

        $this->addFlash('success', 'Offre dupliquée avec succès (statut: Brouillon).');
        return $this->redirectToRoute('app_job_offre_my');
    }

    #[Route('/{id}', name: 'app_job_offre_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, JobOffre $jobOffre, EntityManagerInterface $entityManager, CloudinaryUploader $uploader): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user || ($jobOffre->getUser() && $jobOffre->getUser()->getId() !== $user->getId())) {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres offres.');
            return $this->redirectToRoute('app_job_offre_my');
        }

        if ($this->isCsrfTokenValid('delete' . $jobOffre->getId(), $request->request->get('_token'))) {
            // Supprimer le logo Cloudinary si existant
            if ($jobOffre->getCompanyLogoPublicId()) {
                $uploader->deleteLogo($jobOffre->getCompanyLogoPublicId());
            }
            $entityManager->remove($jobOffre);
            $entityManager->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        }

        return $this->redirectToRoute('app_job_offre_my');
    }

}
