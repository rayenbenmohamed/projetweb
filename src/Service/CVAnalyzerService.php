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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $prompt = <<<PROMPT
Tu es un expert en recrutement RH. Analyse le CV suivant par rapport à la description du poste fournie.
Donne ta réponse UNIQUEMENT sous forme de JSON valide avec les clés suivantes :
- "score": un entier entre 0 et 100 représentant la compatibilité globale.
- "summary": un court résumé (max 200 caractères) des points forts du candidat.
- "recommendation": une recommandation courte (max 200 caractères) (ex: "À recruter", "Profil junior prometteur", "Manque de compétences clés").

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
}

