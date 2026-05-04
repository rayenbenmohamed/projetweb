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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

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
                        'maxOutputTokens' => 2048,
                        'response_mime_type' => 'application/json',
                    ],
                ]
            ]);

            $data = $response->toArray();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return ['error' => 'Réponse inattendue de Gemini (pas de texte généré).'];
            }

            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];

            $result = $this->decodeAiResponse($aiText);

            if (!$result || isset($result['error'])) {
                return [
                    'error' => 'JSON invalide reçu de Gemini' . (isset($result['error']) ? ' : ' . $result['error'] : '') . 
                               ' — Réponse brute complète : ' . $aiText
                ];
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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

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
                    'generationConfig' => [
                        'temperature' => 0.2, 
                        'maxOutputTokens' => 1024,
                        'response_mime_type' => 'application/json'
                    ]
                ]
            ]);

            $data = $response->toArray();
            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
            
            $result = $this->decodeAiResponse($aiText);
            return $result ?: null;
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
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

        try {
            // 1. Télécharger le document avec HttpClient
            $response = $this->httpClient->request('GET', $documentUrl, [
                'timeout' => 30,
            ]);
            
            if ($response->getStatusCode() !== 200) {
                return ['error' => 'Impossible de télécharger le document (Statut: ' . $response->getStatusCode() . ')'];
            }
            
            $fileContent = $response->getContent();
            $base64Data = base64_encode($fileContent);

            // ── Détection robuste du mimeType ──────────────────────────────
            // On nettoie l'URL des paramètres de requête (?v=...)
            $cleanUrl = strtok($documentUrl, '?');
            $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));
            
            $mimeMap = [
                'pdf'  => 'application/pdf',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'webp' => 'image/webp',
                'heic' => 'image/heic',
                'heif' => 'image/heif'
            ];
            
            $mimeType = $mimeMap[$ext] ?? 'application/pdf';

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
                        'maxOutputTokens' => 2048,
                        'response_mime_type' => 'application/json',
                    ],
                ]
            ]);

            $data = $response->toArray(false); // Disable throwing exception on 4xx/5xx to see errors
            if (isset($data['error'])) {
                return ['error' => 'Erreur API Gemini: ' . ($data['error']['message'] ?? 'Inconnue')];
            }
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return ['error' => 'Échec de l\'analyse visuelle par l\'IA (Pas de réponse générée).'];
            }

            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
            
            $result = $this->decodeAiResponse($aiText);
            if ($result) {
                $result['raw_text_analyzed'] = "[Document analysé visuellement par l'IA]";
                return $result;
            }

            return ['error' => 'L\'IA a renvoyé un format illisible.'];

        } catch (\Exception $e) {
            return ['error' => "Erreur de scan automatique : " . $e->getMessage()];
        }
    }

    /**
     * Robustly decodes a JSON string from AI, stripping common noise and control characters.
     */
    private function decodeAiResponse(string $aiText): ?array
    {
        $aiText = trim($aiText);

        // Strip markdown code blocks if present (though response_mime_type should prevent them)
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/m', $aiText, $matches)) {
            $aiText = trim($matches[1]);
        }

        // Strip control characters (0-31 and 127) that break json_decode
        // This handles the "Control character error" specifically.
        $aiText = preg_replace('/[\x00-\x1F\x7F]/', '', $aiText);

        $result = json_decode($aiText, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        // Fallback: try to extract { ... } if there's still noise
        if (preg_match('/\{[\s\S]*\}/m', $aiText, $jsonMatches)) {
            $result = json_decode($jsonMatches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $result;
            }
        }

        return ['error' => json_last_error_msg(), 'debug_text' => substr($aiText, 0, 500)];
    }

    /**
     * Recommends the top 5 job offers from a list based on the candidate's CV.
     */
    public function recommendTopJobs(string $cvText, array $jobOffers): ?array
    {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

        // Prepare a simplified list of jobs to save tokens and focus on ranking
        $jobsList = array_map(function($job) {
            return [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'description' => mb_substr(strip_tags($job->getDescription()), 0, 200) . '...'
            ];
        }, $jobOffers);

        $prompt = <<<PROMPT
Tu es un consultant en carrière senior chez SyfonuRH. Ta mission est de recommander les MEILLEURS postes à un candidat en fonction de son CV.
Analyse le CV ci-dessous et les offres d'emploi disponibles.

EXIGENCE : Sélectionne exactement les 5 meilleures offres (ou moins s'il n'y en a pas assez).
Pour chaque offre sélectionnée, fournis :
- "id": l'ID de l'offre
- "score": un score de compatibilité (0-100)
- "why": une explication très courte et motivante (ex: "Tes compétences en React matchent parfaitement avec leur stack technique").

Donne ta réponse UNIQUEMENT en JSON valide sous la forme d'une liste d'objets.

CV DU CANDIDAT :
$cvText

OFFRES DISPONIBLES :
JSON formaté des offres : 
PROMPT;
        
        $prompt .= json_encode($jobsList);

        try {
            $response = $this->httpClient->request('POST', $apiUrl, [
                'json' => [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 2048,
                        'response_mime_type' => 'application/json',
                    ],
                ]
            ]);

            $data = $response->toArray();
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) return null;

            $aiText = $data['candidates'][0]['content']['parts'][0]['text'];
            return $this->decodeAiResponse($aiText);

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

