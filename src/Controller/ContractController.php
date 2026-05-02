<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\PdfTemplate;
use App\Service\EmailService;
use App\Service\GoogleCalendarService;
use App\Repository\ContractRepository;
use App\Repository\JobOffreRepository;
use App\Repository\TypeContratRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/contract')]
class ContractController extends AbstractController
{
    #[Route('/', name: 'app_contract_index', methods: ['GET'])]
    public function index(
        Request $request,
        ContractRepository $contractRepository,
        TypeContratRepository $typeContratRepository,
        UserRepository $userRepository
    ): Response {
        $filters = [
            'q' => $request->query->get('q', ''),
            'type' => $request->query->get('type', ''),
            'status' => $request->query->get('status', ''),
            'signed' => $request->query->get('signed', ''),
            'salary_min' => $request->query->get('salary_min', ''),
            'candidate_id' => $request->query->get('candidate_id', ''),
            'recruiter_id' => $request->query->get('recruiter_id', ''),
        ];

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        $result = $contractRepository->searchPaginated($filters, $page, $limit);

        $allContracts = $contractRepository->findAll();

        $stats = [
            'total_payroll' => array_reduce($allContracts, fn($carry, $c) => $carry + ($c->getSalary() ?? 0), 0),
            'pending_signatures' => count(array_filter($allContracts, fn($c) => !$c->isSigned())),
            'expiring_soon' => count(array_filter($allContracts, function ($c) {
                if (!$c->getEndDate())
                    return false;
                $diff = (new \DateTime())->diff($c->getEndDate());
                return $diff->invert === 0 && $diff->days <= 30;
            })),
        ];

        $chartData = [
            'types' => [],
            'trends' => array_fill(1, 12, 0)
        ];

        foreach ($allContracts as $c) {
            $typeName = $c->getTypeContrat() ? $c->getTypeContrat()->getName() : 'Autre';
            $chartData['types'][$typeName] = ($chartData['types'][$typeName] ?? 0) + 1;

            if ($c->getStartDate() && $c->getStartDate()->format('Y') == date('Y')) {
                $month = (int) $c->getStartDate()->format('n');
                $chartData['trends'][$month]++;
            }
        }

        return $this->render('contract/index.html.twig', [
            'contracts' => $result['items'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'filters' => $filters,
            'stats' => $stats,
            'chartData' => $chartData,
            'types' => $typeContratRepository->findBy([], ['name' => 'ASC']),
            'all_candidates' => $userRepository->findBy(['role' => 'ROLE_CANDIDAT'], ['firstName' => 'ASC']),
            'all_recruiters' => $userRepository->findBy(['role' => 'ROLE_RECRUTEUR'], ['firstName' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_contract_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        JobOffreRepository $jobOffreRepository,
        TypeContratRepository $typeContratRepository,
        ValidatorInterface $validator,
        EmailService $emailService,
        GoogleCalendarService $calendarService
    ): Response {
        $contract = new Contract();
        $formData = null;

        if ($request->isMethod('POST')) {
            $formData = $this->extractFormData($request);

            // Populate entity
            $candidate = $formData['candidate_id'] ? $userRepository->find((int) $formData['candidate_id']) : null;
            $jobOffre = $formData['job_offre_id'] ? $jobOffreRepository->find((int) $formData['job_offre_id']) : null;
            $recruiter = $formData['recruiter_id'] ? $userRepository->find((int) $formData['recruiter_id']) : null;
            $type = $formData['type_contrat_id'] ? $typeContratRepository->find((int) $formData['type_contrat_id']) : null;
            $pdfTemplate = $formData['pdf_template_id'] ? $entityManager->getRepository(PdfTemplate::class)->find((int) $formData['pdf_template_id']) : null;

            $contract->setCandidate($candidate);
            $contract->setJobOffre($jobOffre);
            $contract->setRecruiter($recruiter);
            $contract->setTypeContrat($type);
            $contract->setPdfTemplate($pdfTemplate);
            $contract->setStatus($formData['status'] ?: 'En Attente');
            $contract->setIsSigned($formData['is_signed'] === '1');
            $contract->setContent($formData['content'] ?: null);

            [$startDateObj, $endDateObj, $dateErrors] = $this->parseDates($formData['start_date'], $formData['end_date']);

            if ($startDateObj) {
                $contract->setStartDate($startDateObj);
            }
            $contract->setEndDate($endDateObj);

            if ($formData['salary'] !== '' && is_numeric($formData['salary'])) {
                $contract->setSalary((int) $formData['salary']);
            }

            if ($formData['signature_base64'] !== '') {
                $contract->setSignatureBase64($formData['signature_base64']);
            }

            // Validate via entity constraints
            $violations = $validator->validate($contract);

            $allErrors = array_merge($dateErrors, iterator_to_array($violations, false));

            if (count($allErrors) > 0) {
                foreach ($allErrors as $error) {
                    if (is_string($error)) {
                        $this->addFlash('error', $error);
                    } else {
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            } else {
                $contract->setSignedAt($contract->isSigned() ? new \DateTime() : null);

                $entityManager->persist($contract);
                $entityManager->flush();

                // ── Google Calendar Sync ──
                $user = $this->getUser();
                if ($user instanceof \App\Entity\User && $user->getGoogleAccessToken()) {
                    $calendarService->syncContract($contract, $user);
                }

                $this->addFlash('success', 'Contrat créé avec succès.');
                return $this->redirectToRoute('app_contract_show', ['id' => $contract->getId(), 'send_email' => 1]);
            }
        }

        return $this->render('contract/new.html.twig', [
            'contract' => $contract,
            'candidates' => $userRepository->findBy(['role' => 'ROLE_CANDIDAT'], ['id' => 'DESC']),
            'recruiters' => $userRepository->findBy(['role' => 'ROLE_RECRUTEUR'], ['id' => 'DESC']),
            'job_offres' => $jobOffreRepository->findBy([], ['createdAt' => 'DESC']),
            'types' => $typeContratRepository->findBy([], ['name' => 'ASC']),
            'pdf_templates' => $entityManager->getRepository(PdfTemplate::class)->findAll(),
            'form' => $formData,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Contract $contract,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        JobOffreRepository $jobOffreRepository,
        TypeContratRepository $typeContratRepository,
        ValidatorInterface $validator,
        GoogleCalendarService $calendarService
    ): Response {
        $formData = null;

        if ($request->isMethod('POST')) {
            $formData = $this->extractFormData($request);

            $candidate = $formData['candidate_id'] ? $userRepository->find((int) $formData['candidate_id']) : null;
            $jobOffre = $formData['job_offre_id'] ? $jobOffreRepository->find((int) $formData['job_offre_id']) : null;
            $recruiter = $formData['recruiter_id'] ? $userRepository->find((int) $formData['recruiter_id']) : null;
            $type = $formData['type_contrat_id'] ? $typeContratRepository->find((int) $formData['type_contrat_id']) : null;
            $pdfTemplate = $formData['pdf_template_id'] ? $entityManager->getRepository(PdfTemplate::class)->find((int) $formData['pdf_template_id']) : null;

            $contract->setCandidate($candidate);
            $contract->setJobOffre($jobOffre);
            $contract->setRecruiter($recruiter);
            $contract->setTypeContrat($type);
            $contract->setPdfTemplate($pdfTemplate);
            $contract->setStatus($formData['status'] ?: 'En Attente');
            $isSigned = $formData['is_signed'] === '1';
            $contract->setIsSigned($isSigned);
            $contract->setContent($formData['content'] ?: null);

            [$startDateObj, $endDateObj, $dateErrors] = $this->parseDates($formData['start_date'], $formData['end_date']);

            if ($startDateObj) {
                $contract->setStartDate($startDateObj);
            }
            $contract->setEndDate($endDateObj);

            if ($formData['salary'] !== '' && is_numeric($formData['salary'])) {
                $contract->setSalary((int) $formData['salary']);
            }

            if ($formData['signature_base64'] !== '') {
                $contract->setSignatureBase64($formData['signature_base64']);
            }

            $violations = $validator->validate($contract);

            $allErrors = array_merge($dateErrors, iterator_to_array($violations, false));

            if (count($allErrors) > 0) {
                foreach ($allErrors as $error) {
                    if (is_string($error)) {
                        $this->addFlash('error', $error);
                    } else {
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            } else {
                $contract->setSignedAt($isSigned ? ($contract->getSignedAt() ?? new \DateTime()) : null);

                $entityManager->flush();

                // ── Google Calendar Update Sync ──
                $user = $this->getUser();
                if ($user instanceof \App\Entity\User && $user->getGoogleAccessToken()) {
                    // Note: This adds new events, real sync would need event IDs to update existing ones.
                    // For now, we just push new ones as requested.
                    $calendarService->syncContract($contract, $user);
                }

                $this->addFlash('success', 'Contrat mis à jour.');
                return $this->redirectToRoute('app_contract_show', ['id' => $contract->getId(), 'send_email' => 1]);
            }
        }

        return $this->render('contract/edit.html.twig', [
            'contract' => $contract,
            'candidates' => $userRepository->findBy(['role' => 'ROLE_CANDIDAT'], ['id' => 'DESC']),
            'recruiters' => $userRepository->findBy(['role' => 'ROLE_RECRUTEUR'], ['id' => 'DESC']),
            'job_offres' => $jobOffreRepository->findBy([], ['createdAt' => 'DESC']),
            'types' => $typeContratRepository->findBy([], ['name' => 'ASC']),
            'pdf_templates' => $entityManager->getRepository(PdfTemplate::class)->findAll(),
            'form' => $formData,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show', methods: ['GET'])]
    public function show(Contract $contract, GoogleCalendarService $calendarService): Response
    {
        $user = $this->getUser();
        $googleEvents = [];
        $isGoogleLinked = false;

        if ($user instanceof \App\Entity\User && method_exists($user, 'getGoogleAccessToken')) {
            $isGoogleLinked = $user->getGoogleAccessToken() !== null;
            if ($isGoogleLinked) {
                $googleEvents = $calendarService->getUpcomingEvents($user);
            }
        }

        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
            'googleEvents' => $googleEvents,
            'isGoogleLinked' => $isGoogleLinked,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_delete', methods: ['POST'])]
    public function delete(Request $request, Contract $contract, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_contract_' . $contract->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($contract);
            $entityManager->flush();
            $this->addFlash('success', 'Contrat supprimé.');
        }

        return $this->redirectToRoute('app_contract_index');
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function extractFormData(Request $request): array
    {
        return [
            'candidate_id' => (string) $request->request->get('candidate_id', ''),
            'recruiter_id' => (string) $request->request->get('recruiter_id', ''),
            'job_offre_id' => (string) $request->request->get('job_offre_id', ''),
            'type_contrat_id' => (string) $request->request->get('type_contrat_id', ''),
            'start_date' => (string) $request->request->get('start_date', ''),
            'end_date' => (string) $request->request->get('end_date', ''),
            'salary' => (string) $request->request->get('salary', ''),
            'status' => (string) $request->request->get('status', 'En Attente'),
            'is_signed' => $request->request->get('is_signed') === '1' ? '1' : '0',
            'signature_base64' => (string) $request->request->get('signature_base64', ''),
            'content' => (string) $request->request->get('content', ''),
            'pdf_template_id' => (string) $request->request->get('pdf_template_id', ''),
        ];
    }

    /**
     * @return array{0: ?\DateTime, 1: ?\DateTime, 2: string[]}
     */
    private function parseDates(string $startRaw, string $endRaw): array
    {
        $errors = [];
        $startObj = null;
        $endObj = null;

        try {
            if ($startRaw !== '') {
                $startObj = new \DateTime($startRaw);
            }
            if ($endRaw !== '') {
                $endObj = new \DateTime($endRaw);
            }
        } catch (\Throwable) {
            $errors[] = 'Format de date invalide.';
        }

        if ($startObj && $endObj && $endObj < $startObj) {
            $errors[] = 'La date de fin doit être postérieure à la date de début.';
        }

        return [$startObj, $endObj, $errors];
    }
}
