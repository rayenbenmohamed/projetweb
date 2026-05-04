<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AiInterviewCoachService;
use App\Service\CloudinaryService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/ai-coach')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class AiCoachController extends AbstractController
{
    private const SESSION_KEY = 'ai_coach_history';

    #[Route('', name: 'app_ai_coach', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->requireUser();
        $history = (array) $request->getSession()->get(self::SESSION_KEY, []);

        return $this->render('ai_coach/index.html.twig', [
            'history' => $history,
        ]);
    }

    #[Route('/message', name: 'app_ai_coach_message', methods: ['POST'])]
    public function message(Request $request, AiInterviewCoachService $coachService): JsonResponse
    {
        $this->requireUser();

        if (!$this->isCsrfTokenValid('ai_coach_message', (string) $request->request->get('_token'))) {
            return $this->json(['ok' => false, 'error' => 'Session expirée. Recharge la page.'], 400);
        }

        $message = trim((string) $request->request->get('message'));
        if ($message === '') {
            return $this->json(['ok' => false, 'error' => 'Écris une question avant d’envoyer.'], 400);
        }
        if (mb_strlen($message) > 2000) {
            return $this->json(['ok' => false, 'error' => 'Message trop long (max 2000 caractères).'], 400);
        }

        $session = $request->getSession();
        $history = (array) $session->get(self::SESSION_KEY, []);
        $history[] = ['role' => 'user', 'content' => $message];

        $reply = $coachService->generateReply($message, $history);
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $session->set(self::SESSION_KEY, array_slice($history, -20));

        return $this->json([
            'ok' => true,
            'assistant' => $reply,
        ]);
    }

    #[Route('/reset', name: 'app_ai_coach_reset', methods: ['POST'])]
    public function reset(Request $request): Response
    {
        $this->requireUser();
        if ($this->isCsrfTokenValid('ai_coach_reset', (string) $request->request->get('_token'))) {
            $request->getSession()->remove(self::SESSION_KEY);
            $this->addFlash('success', 'Conversation réinitialisée.');
        }

        return $this->redirectToRoute('app_ai_coach');
    }

    #[Route('/enhance-photo', name: 'app_ai_coach_enhance_photo', methods: ['POST'])]
    public function enhancePhoto(Request $request, CloudinaryService $cloudinaryService): JsonResponse
    {
        $this->requireUser();

        if (!$this->isCsrfTokenValid('ai_coach_enhance_photo', (string) $request->request->get('_token'))) {
            return $this->json(['ok' => false, 'error' => 'Session expirée. Recharge la page.'], 400);
        }

        $photo = $request->files->get('photo');
        if (!$photo instanceof UploadedFile) {
            return $this->json(['ok' => false, 'error' => 'Choisis une photo avant d’envoyer.'], 400);
        }

        if (!$photo->isValid()) {
            return $this->json(['ok' => false, 'error' => 'Le fichier est invalide. Réessaie.'], 400);
        }

        $mimeType = (string) $photo->getMimeType();
        if (!str_starts_with($mimeType, 'image/')) {
            return $this->json(['ok' => false, 'error' => 'Seules les images sont acceptées.'], 400);
        }

        if ((int) $photo->getSize() > 8 * 1024 * 1024) {
            return $this->json(['ok' => false, 'error' => 'Image trop lourde (max 8 Mo).'], 400);
        }

        try {
            $uploaded = $cloudinaryService->uploadFileDetailed($photo, 'ai-coach');
            $originalUrl = (string) ($uploaded['secure_url'] ?? '');
            if ($originalUrl === '') {
                return $this->json(['ok' => false, 'error' => 'Upload impossible pour le moment.'], 500);
            }

            $enhancedUrl = $cloudinaryService->getProfessionalPhotoUrl($originalUrl);
        } catch (\Throwable) {
            return $this->json(['ok' => false, 'error' => 'Erreur pendant le traitement de la photo.'], 500);
        }

        $session = $request->getSession();
        $history = (array) $session->get(self::SESSION_KEY, []);
        $history[] = ['role' => 'user', 'content' => '[Photo envoyée pour amélioration professionnelle]'];
        $history[] = ['role' => 'assistant', 'content' => "J'ai amélioré ta photo pour un rendu plus professionnel (cadrage visage + netteté + qualité optimisée)."];
        $session->set(self::SESSION_KEY, array_slice($history, -20));

        return $this->json([
            'ok' => true,
            'assistant' => "Voici ta version améliorée. Si tu veux, je peux aussi te conseiller la meilleure pour LinkedIn et CV.",
            'original_url' => $originalUrl,
            'enhanced_url' => $enhancedUrl,
        ]);
    }

    #[Route('/export-pdf', name: 'app_ai_coach_export_pdf', methods: ['POST'])]
    public function exportPdf(Request $request): Response
    {
        $user = $this->requireUser();
        if (!$this->isCsrfTokenValid('ai_coach_export_pdf', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée. Recharge la page puis réessaie.');

            return $this->redirectToRoute('app_ai_coach');
        }

        $history = (array) $request->getSession()->get(self::SESSION_KEY, []);
        if ($history === []) {
            $this->addFlash('warning', 'Aucune conversation à exporter.');

            return $this->redirectToRoute('app_ai_coach');
        }

        $rows = '';
        foreach ($history as $item) {
            $role = (string) ($item['role'] ?? 'user');
            $label = $role === 'assistant' ? 'Coach IA' : 'Vous';
            $content = htmlspecialchars((string) ($item['content'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeContent = nl2br($content);
            $rows .= sprintf(
                '<tr><td class="role">%s</td><td class="content">%s</td></tr>',
                htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                $safeContent
            );
        }

        $title = 'Conversation Coach IA - ' . ($user->getEmail() ?? 'utilisateur');
        $html = sprintf(
            '<html><head><meta charset="UTF-8"><style>
                body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#222;margin:24px;}
                h1{font-size:18px;margin:0 0 8px;}
                .meta{color:#666;margin:0 0 18px;}
                table{width:100%%;border-collapse:collapse;}
                td{border:1px solid #ddd;vertical-align:top;padding:8px;}
                .role{width:110px;font-weight:700;background:#f5f5f5;}
                .content{white-space:normal;word-wrap:break-word;}
            </style></head><body>
            <h1>%s</h1>
            <div class="meta">Exporté le %s</div>
            <table>%s</table>
            </body></html>',
            htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            (new \DateTimeImmutable())->format('d/m/Y H:i'),
            $rows
        );

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'ai-coach-conversation-' . (new \DateTimeImmutable())->format('Ymd-His') . '.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    private function requireUser(): User
    {
        $u = $this->getUser();
        if (!$u instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $u;
    }
}

