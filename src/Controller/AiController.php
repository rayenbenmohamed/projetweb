<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class AiController extends AbstractController
{
    #[Route('/generate-pdf-style', name: 'api_ai_generate_pdf_style', methods: ['POST'])]
    public function generatePdfStyle(Request $request, GeminiService $geminiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? '';

        if (empty($prompt)) {
            return new JsonResponse(['error' => 'Le prompt ne peut pas être vide.'], 400);
        }

        try {
            $primaryColor = $data['primaryColor'] ?? null;
            $secondaryColor = $data['secondaryColor'] ?? null;

            $result = $geminiService->generatePdfTemplate($prompt, $primaryColor, $secondaryColor);
            
            return new JsonResponse([
                'header' => $result['header'],
                'body' => $result['body'],
                'footer' => $result['footer'],
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => "Erreur IA: " . $e->getMessage()
            ], 500);
        }
    }
}
