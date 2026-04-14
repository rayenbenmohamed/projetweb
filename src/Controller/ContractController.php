<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Repository\JobOffreRepository;
use App\Repository\TypeContratRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        // --- Statistiques Innovantes pour le Dashboard ---
        $allContracts = $contractRepository->findAll();
        
        // 1. KPIs classiques
        $stats = [
            'total_payroll' => array_reduce($allContracts, fn($carry, $c) => $carry + ($c->getSalary() ?? 0), 0),
            'pending_signatures' => count(array_filter($allContracts, fn($c) => !$c->isSigned())),
            'expiring_soon' => count(array_filter($allContracts, function($c) {
                if (!$c->getEndDate()) return false;
                $diff = (new \DateTime())->diff($c->getEndDate());
                return $diff->invert === 0 && $diff->days <= 30;
            })),
        ];

        // 2. Données pour le Graphique (ChartData)
        $chartData = [
            'types' => [],
            'trends' => array_fill(1, 12, 0) // Jan..Dec
        ];
        
        foreach ($allContracts as $c) {
            // Distribution par type
            $typeName = $c->getTypeContrat() ? $c->getTypeContrat()->getName() : 'Autre';
            $chartData['types'][$typeName] = ($chartData['types'][$typeName] ?? 0) + 1;
            
            // Tendance annuelle
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
        TypeContratRepository $typeContratRepository
    ): Response {
        $contract = new Contract();
        $form = null;

        if ($request->isMethod('POST')) {
            $candidateId = $request->request->get('candidate_id');
            $recruiterId = $request->request->get('recruiter_id');
            $jobOffreId = $request->request->get('job_offre_id');
            $typeId = $request->request->get('type_contrat_id');

            $candidate = $candidateId ? $userRepository->find((int) $candidateId) : null;
            $jobOffre = $jobOffreId ? $jobOffreRepository->find((int) $jobOffreId) : null;
            $recruiter = $recruiterId ? $userRepository->find((int) $recruiterId) : null;
            $type = $typeId ? $typeContratRepository->find((int) $typeId) : null;

            $form = [
                'candidate_id' => (string) $candidateId,
                'recruiter_id' => (string) $recruiterId,
                'job_offre_id' => (string) $jobOffreId,
                'type_contrat_id' => (string) $typeId,
                'start_date' => (string) $request->request->get('start_date', ''),
                'end_date' => (string) $request->request->get('end_date', ''),
                'salary' => (string) $request->request->get('salary', ''),
                'status' => (string) $request->request->get('status', 'En Attente'),
                'is_signed' => $request->request->get('is_signed') === '1' ? '1' : '0',
                'signature_base64' => (string) $request->request->get('signature_base64', ''),
                'content' => (string) $request->request->get('content', ''),
            ];

            $errors = [];
            if (!$candidate) {
                $errors[] = 'Veuillez sélectionner un candidat.';
            }
            if (!$jobOffre) {
                $errors[] = 'Veuillez sélectionner une offre.';
            }
            if ($form['start_date'] === '') {
                $errors[] = 'La date de début est obligatoire.';
            }
            if ($form['salary'] === '' || !is_numeric($form['salary']) || (int) $form['salary'] < 0) {
                $errors[] = 'Le salaire doit être un nombre positif.';
            }

            $allowedStatuses = ['En Attente', 'Actif', 'Suspendu', 'Terminé'];
            if (!in_array($form['status'], $allowedStatuses, true)) {
                $errors[] = 'Statut invalide.';
            }

            $startDateObj = null;
            $endDateObj = null;
            try {
                if ($form['start_date'] !== '') {
                    $startDateObj = new \DateTime($form['start_date']);
                }
                if ($form['end_date'] !== '') {
                    $endDateObj = new \DateTime($form['end_date']);
                }
            } catch (\Throwable) {
                $errors[] = 'Format de date invalide.';
            }

            if ($startDateObj && $endDateObj && $endDateObj < $startDateObj) {
                $errors[] = 'La date de fin doit être après la date de début.';
            }

            if ($errors !== []) {
                foreach ($errors as $e) {
                    $this->addFlash('error', $e);
                }
            } else {
                $contract->setCandidate($candidate);
                $contract->setRecruiter($recruiter);
                $contract->setJobOffre($jobOffre);
                $contract->setTypeContrat($type);

                $isSigned = $form['is_signed'] === '1';
                $contract->setStartDate($startDateObj ?? new \DateTime());
                $contract->setEndDate($endDateObj);
                $contract->setSalary((int) $form['salary']);
                $contract->setStatus($form['status']);
                $contract->setIsSigned($isSigned);
                $contract->setSignedAt($isSigned ? new \DateTime() : null);
                if ($form['signature_base64'] !== '') {
                    $contract->setSignatureBase64($form['signature_base64']);
                }
                $contract->setContent($form['content']);

                $entityManager->persist($contract);
                $entityManager->flush();

                $this->addFlash('success', 'Contrat créé avec succès.');
                return $this->redirectToRoute('app_contract_index');
            }
        }

        return $this->render('contract/new.html.twig', [
            'contract' => $contract,
            'candidates' => $userRepository->findBy(['role' => 'ROLE_CANDIDAT'], ['id' => 'DESC']),
            'recruiters' => $userRepository->findBy(['role' => 'ROLE_RECRUTEUR'], ['id' => 'DESC']),
            'job_offres' => $jobOffreRepository->findBy([], ['createdAt' => 'DESC']),
            'types' => $typeContratRepository->findBy([], ['name' => 'ASC']),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contract_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Contract $contract,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        JobOffreRepository $jobOffreRepository,
        TypeContratRepository $typeContratRepository
    ): Response {
        $form = null;
        if ($request->isMethod('POST')) {
            $candidateId = $request->request->get('candidate_id');
            $recruiterId = $request->request->get('recruiter_id');
            $jobOffreId = $request->request->get('job_offre_id');
            $typeId = $request->request->get('type_contrat_id');

            $candidate = $candidateId ? $userRepository->find((int) $candidateId) : null;
            $jobOffre = $jobOffreId ? $jobOffreRepository->find((int) $jobOffreId) : null;
            $recruiter = $recruiterId ? $userRepository->find((int) $recruiterId) : null;
            $type = $typeId ? $typeContratRepository->find((int) $typeId) : null;

            $form = [
                'candidate_id' => (string) $candidateId,
                'recruiter_id' => (string) $recruiterId,
                'job_offre_id' => (string) $jobOffreId,
                'type_contrat_id' => (string) $typeId,
                'start_date' => (string) $request->request->get('start_date', ''),
                'end_date' => (string) $request->request->get('end_date', ''),
                'salary' => (string) $request->request->get('salary', ''),
                'status' => (string) $request->request->get('status', 'En Attente'),
                'is_signed' => $request->request->get('is_signed') === '1' ? '1' : '0',
                'signature_base64' => (string) $request->request->get('signature_base64', ''),
                'content' => (string) $request->request->get('content', ''),
            ];

            $errors = [];
            if (!$candidate) {
                $errors[] = 'Veuillez sélectionner un candidat.';
            }
            if (!$jobOffre) {
                $errors[] = 'Veuillez sélectionner une offre.';
            }
            if ($form['start_date'] === '') {
                $errors[] = 'La date de début est obligatoire.';
            }
            if ($form['salary'] === '' || !is_numeric($form['salary']) || (int) $form['salary'] < 0) {
                $errors[] = 'Le salaire doit être un nombre positif.';
            }

            $allowedStatuses = ['En Attente', 'Actif', 'Suspendu', 'Terminé'];
            if (!in_array($form['status'], $allowedStatuses, true)) {
                $errors[] = 'Statut invalide.';
            }

            $startDateObj = null;
            $endDateObj = null;
            try {
                if ($form['start_date'] !== '') {
                    $startDateObj = new \DateTime($form['start_date']);
                }
                if ($form['end_date'] !== '') {
                    $endDateObj = new \DateTime($form['end_date']);
                }
            } catch (\Throwable) {
                $errors[] = 'Format de date invalide.';
            }

            if ($startDateObj && $endDateObj && $endDateObj < $startDateObj) {
                $errors[] = 'La date de fin doit être après la date de début.';
            }

            if ($errors !== []) {
                foreach ($errors as $e) {
                    $this->addFlash('error', $e);
                }
            } else {
                $contract->setCandidate($candidate);
                $contract->setRecruiter($recruiter);
                $contract->setJobOffre($jobOffre);
                $contract->setTypeContrat($type);

                $isSigned = $form['is_signed'] === '1';
                if ($startDateObj) {
                    $contract->setStartDate($startDateObj);
                }
                $contract->setEndDate($endDateObj);
                $contract->setSalary((int) $form['salary']);
                $contract->setStatus($form['status']);
                $contract->setIsSigned($isSigned);
                $contract->setSignedAt($isSigned ? ($contract->getSignedAt() ?? new \DateTime()) : null);
                if ($form['signature_base64'] !== '') {
                    $contract->setSignatureBase64($form['signature_base64']);
                }
                $contract->setContent($form['content']);

                $entityManager->flush();

                $this->addFlash('success', 'Contrat mis à jour.');
                return $this->redirectToRoute('app_contract_show', ['id' => $contract->getId()]);
            }
        }

        return $this->render('contract/edit.html.twig', [
            'contract' => $contract,
            'candidates' => $userRepository->findBy(['role' => 'ROLE_CANDIDAT'], ['id' => 'DESC']),
            'recruiters' => $userRepository->findBy(['role' => 'ROLE_RECRUTEUR'], ['id' => 'DESC']),
            'job_offres' => $jobOffreRepository->findBy([], ['createdAt' => 'DESC']),
            'types' => $typeContratRepository->findBy([], ['name' => 'ASC']),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contract_show', methods: ['GET'])]
    public function show(Contract $contract): Response
    {
        return $this->render('contract/show.html.twig', [
            'contract' => $contract,
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
}
