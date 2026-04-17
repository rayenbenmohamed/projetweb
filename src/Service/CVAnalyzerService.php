<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CVAnalyzerService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $geminiApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $geminiApiKey;
    }

    /**
     * Analyzes a CV text against a job description using Gemini AI.
     *
     * @param string $jobDescription The detailed description of the job offer.
     * @param string $cvText         The extracted text from the candidate's CV.
     * @return array|null An array with 'score', 'summary', and 'recommendation', or null on failure.
     */
    public function analyze(string $jobDescription, string $cvText): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $this->apiKey;

        $prompt = <<<PROMPT
Tu es un expert en recrutement RH senior hautement analytique. Analyse le CV suivant par rapport à la description du poste fournie.
Ta mission est de fournir une évaluation PERSONNALISÉE et CRITIQUE du candidat.

Donne ta réponse UNIQUEMENT sous forme de JSON valide avec les clés suivantes :
- "score": un entier entre 0 et 100 représentant la compatibilité globale.
- "score_breakdown": un objet avec les scores suivants (0-100) :
    - "technical": Maîtrise des outils et langages requis.
    - "experience": Pertinence du parcours et des responsabilités passées.
    - "soft_skills": Signaux d'intelligence relationnelle et d'autonomie.
    - "potential": Capacité d'évolution et d'apprentissage.
- "summary": un résumé TRÈS PERSONNALISÉ du profil (max 250 caractères). Évite les généralités.
- "verdict": un objet avec :
    - "pros": un tableau des 3 points forts principaux.
    - "cons": un tableau des 2 points de vigilance ou manques.
- "recommendation": une recommandation stratégique courte (ex: "À recruter immédiatement", "Profil intéressant mais manque de seniorité", "Ne pas retenir").

Ne réponds qu'avec le JSON, sans texte avant ni après, sans bloc markdown.

DESCRIPTION DU POSTE :
$jobDescription

TEXTE DU CV :
$cvText
PROMPT;

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.2,
                        'maxOutputTokens' => 512,
                    ],
                ]
            ]);

            $data = $response->toArray();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return ['error' => 'Réponse inattendue de Gemini (pas de texte généré).'];
            }

            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];

            // ── Nettoyage robuste des balises markdown ────────────────────────
            $aiText = trim($aiText);

            // Supprimer ```json ... ``` ou ``` ... ```
            if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/m', $aiText, $matches)) {
                $aiText = trim($matches[1]);
            }

            // Extraire le premier objet JSON s'il y a du texte autour
            if (preg_match('/\{[\s\S]*\}/m', $aiText, $jsonMatches)) {
                $aiText = $jsonMatches[0];
            }

            $result = json_decode($aiText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'JSON invalide reçu de Gemini : ' . json_last_error_msg() . ' — Réponse brute : ' . substr($aiText, 0, 200)];
            }

            // Ajouter le texte brut pour le diagnostic UI
            $result['raw_text_analyzed'] = mb_substr($cvText, 0, 4000);

            return $result;

        } catch (\Exception $e) {
            return [
                'error' => "Erreur de communication avec l'IA : " . $e->getMessage()
            ];
        }
    }

    /**
     * Suggère une évaluation après entretien.
     */
    public function suggestEvaluation(string $jobDescription, string $cvText, string $recruiterNotes): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $prompt = <<<PROMPT
Tu es un recruteur senior. Analyse la cohérence entre le poste, le CV et les notes prises pendant l'entretien.
Donne tes suggestions UNIQUEMENT sous forme de JSON :
- "technicalRating": entier (1-5)
- "communicationRating": entier (1-5) 
- "motivationRating": entier (1-5)
- "suggestedVerdict": une synthèse finale de 2-3 phrases.

POSTE : $jobDescription
CV : $cvText
NOTES RECRUTEUR : $recruiterNotes
PROMPT;

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 512]
                ]
            ]);

            $data = $response->toArray();
            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
            
            if (preg_match('/\{[\s\S]*\}/m', $aiText, $jsonMatches)) {
                return json_decode($jsonMatches[0], true);
            }
            return null;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    /**
     * Analyzes a document (PDF/Image) directly using Gemini's multimodal capabilities.
     * This is the "True Scan" method that works even for scanned images.
     */
    public function analyzeDocument(string $jobDescription, string $documentUrl): ?array
    {
        $apiUrl = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $this->apiKey;

        try {
            // 1. Télécharger le document
            $fileContent = file_get_contents($documentUrl);
            if (!$fileContent) {
                return ['error' => 'Impossible de télécharger le document depuis Cloudinary.'];
            }
            $base64Data = base64_encode($fileContent);

            // 2. Préparer le prompt enrichi
            $prompt = <<<PROMPT
Tu es un expert en recrutement RH senior hautement analytique. 
Analyse le DOCUMENT joint (CV) par rapport à la description du poste fournie ci-dessous.
Ta mission est de fournir une évaluation PERSONNALISÉE et CRITIQUE du candidat, même si le document est un scan ou une image.

Donne ta réponse UNIQUEMENT sous forme de JSON valide avec les clés suivantes :
- "score": un entier entre 0 et 100 représentant la compatibilité globale.
- "score_breakdown": un objet avec les scores suivants (0-100) :
    - "technical": Maîtrise des outils et langages requis.
    - "experience": Pertinence du parcours et des responsabilités passées.
    - "soft_skills": Signaux d'intelligence relationnelle et d'autonomie.
    - "potential": Capacité d'évolution et d'apprentissage.
- "summary": un résumé TRÈS PERSONNALISÉ du profil (max 250 caractères).
- "verdict": un objet avec :
    - "pros": un tableau des 3 points forts principaux.
    - "cons": un tableau des 2 points de vigilance ou manques.
- "recommendation": une recommandation stratégique courte (ex: "À recruter immédiatement", "Profil intéressant mais manque de seniorité", "Ne pas retenir").

DESCRIPTION DU POSTE :
$jobDescription
PROMPT;

            // Détection du mimeType basé sur l'extension Cloudinary
            $mimeType = 'application/pdf';
            if (preg_match('/\.(jpg|jpeg|png)$/i', $documentUrl)) {
                $ext = strtolower(pathinfo($documentUrl, PATHINFO_EXTENSION));
                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
            }

            // 3. Appel API Gemini Multimodal
            $response = $this->httpClient->request('POST', $apiUrl, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Data
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 1024,
                    ],
                ]
            ]);

            $data = $response->toArray();
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return ['error' => 'Échec de l\'analyse visuelle par l\'IA.'];
            }

            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
            
            // Nettoyage et décodage JSON
            if (preg_match('/\{[\s\S]*\}/m', $aiText, $jsonMatches)) {
                $result = json_decode($jsonMatches[0], true);
                if ($result) {
                    $result['raw_text_analyzed'] = "[Document analysé visuellement par l'IA]";
                    return $result;
                }
            }

            return ['error' => 'L\'IA a renvoyé un format illisible.'];

        } catch (\Exception $e) {
            return ['error' => "Erreur de scan automatique : " . $e->getMessage()];
        }
    }
}

