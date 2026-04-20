<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Contract;
use App\Entity\Recruiter;
use App\Service\PdfGeneratorService;
use App\Service\PdfTemplateService;
use App\Service\EmailService;
use App\Service\WhatsAppService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/candidates/create', name: 'api_candidate_create', methods: ['POST'])]
    public function createCandidate(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['email']) || empty($data['firstName']) || empty($data['lastName'])) {
            return new JsonResponse(['error' => 'Données invalides. Champs obligatoires : Prénom, Nom, Email.'], 400);
        }

        // Vérifier si l'email existe déjà
        $existing = $em->getRepository(Candidat::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], 400);
        }

        $candidate = new Candidat();
        $candidate->setEmail($data['email']);
        $candidate->setFirstName($data['firstName']);
        $candidate->setLastName($data['lastName']);
        $candidate->setPhone($data['phone'] ?? null);
        $candidate->setRole('Candidat');
        
        // Mot de passe par défaut
        $password = bin2hex(random_bytes(8));
        $candidate->setPassword($hasher->hashPassword($candidate, $password));

        $em->persist($candidate);
        $em->flush();

        return new JsonResponse([
            'id' => $candidate->getId(),
            'name' => $candidate->getFirstName() . ' ' . $candidate->getLastName(),
            'email' => $candidate->getEmail()
        ]);
    }

    #[Route('/recruiters/create', name: 'api_recruiter_create', methods: ['POST'])]
    public function createRecruiter(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['email']) || empty($data['firstName']) || empty($data['lastName'])) {
            return new JsonResponse(['error' => 'Données invalides. Champs obligatoires : Prénom, Nom, Email.'], 400);
        }

        // Vérifier si l'email existe déjà
        $existing = $em->getRepository(Recruiter::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], 400);
        }

        $recruiter = new Recruiter();
        $recruiter->setEmail($data['email']);
        $recruiter->setFirstName($data['firstName']);
        $recruiter->setLastName($data['lastName']);
        $recruiter->setPhone($data['phone'] ?? null);
        $recruiter->setCompanyname($data['company'] ?? null);
        $recruiter->setDepartement($data['department'] ?? null);
        $recruiter->setRole('Recruteur');

        // Mot de passe par défaut
        $password = bin2hex(random_bytes(8));
        $recruiter->setPassword($hasher->hashPassword($recruiter, $password));

        $em->persist($recruiter);
        $em->flush();

        return new JsonResponse([
            'id' => $recruiter->getId(),
            'name' => $recruiter->getFirstName() . ' ' . $recruiter->getLastName(),
            'email' => $recruiter->getEmail()
        ]);
    }

    #[Route('/candidates/{id}/phone', name: 'api_candidate_update_phone', methods: ['PATCH'])]
    public function updateCandidatePhone(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $candidate = $em->getRepository(Candidat::class)->find($id);
        if (!$candidate) {
            return new JsonResponse(['error' => 'Candidat introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $phone = trim($data['phone'] ?? '');

        if (empty($phone)) {
            return new JsonResponse(['error' => 'Numéro de téléphone vide.'], 400);
        }

        // Normalize: always store as +216XXXXXXXX
        $phone = preg_replace('/[\s\-]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+216' . ltrim($phone, '0');
        }

        $candidate->setPhone($phone);
        $em->flush();

        return new JsonResponse(['success' => true, 'phone' => $candidate->getPhone()]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WhatsApp Business API: send contract PDF
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/contracts/{id}/send-whatsapp', name: 'api_contract_send_whatsapp', methods: ['POST'])]
    public function sendContractWhatsApp(
        int $id,
        EntityManagerInterface $em,
        WhatsAppService $whatsApp,
        PdfGeneratorService $pdfGenerator
    ): JsonResponse {
        /** @var Contract|null $contract */
        $contract = $em->getRepository(Contract::class)->find($id);
        if (!$contract) {
            return new JsonResponse(['error' => 'Contrat introuvable.'], 404);
        }

        $candidate = $contract->getCandidate();
        if (!$candidate || !$candidate->getPhone()) {
            return new JsonResponse([
                'error' => 'Le candidat n\'a pas de numéro de téléphone enregistré.',
            ], 400);
        }

        // ── ENHANCED: Use Centralized PDF Generator ──
        $pdfContent = $pdfGenerator->generateContractPdf($contract);

        // ── Send via WhatsApp API ──
        $result = $whatsApp->sendContractPdf($contract, $pdfContent, $candidate->getPhone());

        if ($result['success']) {
            return new JsonResponse([
                'success'   => true,
                'message'   => 'Contrat envoyé avec succès via WhatsApp Business.',
                'messageId' => $result['messageId'],
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'error'   => $result['error'] ?? 'Erreur lors de l\'envoi WhatsApp.',
        ], 500);
    }

    #[Route('/contracts/{id}/send-email', name: 'api_contract_send_email', methods: ['POST'])]
    public function sendContractEmail(
        int $id,
        EntityManagerInterface $em,
        EmailService $emailService
    ): JsonResponse {
        /** @var Contract|null $contract */
        $contract = $em->getRepository(Contract::class)->find($id);
        if (!$contract) {
            return new JsonResponse(['error' => 'Contrat introuvable.'], 404);
        }

        // ── ENHANCED: Use Automated Method from Service ──
        $result = $emailService->sendContractEmailAutomatically($contract);

        if ($result['success']) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Contrat envoyé avec succès par e-mail.',
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'error'   => $result['error'] ?? 'Erreur lors de l\'envoi de l\'e-mail.',
        ], 500);
    }
}
