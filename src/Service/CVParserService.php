<?php

namespace App\Service;

use Smalot\PdfParser\Parser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CVParserService
{
    private Parser $parser;
    private HttpClientInterface $httpClient;
    private string $geminiApiKey;

    public function __construct(HttpClientInterface $httpClient, string $geminiApiKey)
    {
        $this->parser = new Parser();
        $this->httpClient = $httpClient;
        $this->geminiApiKey = $geminiApiKey;
    }

    /**
     * General method to extract text from a CV document (PDF or Image).
     */
    public function extractText(string $filePath): string
    {
        $cleanUrl = strtok($filePath, '?');
        $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'])) {
            return $this->extractTextFromImage($filePath);
        }

        return $this->extractTextFromPdf($filePath);
    }

    /**
     * Extracts text from a PDF file (local path or remote URL like Cloudinary).
     *
     * @param string $filePath Local path or HTTPS URL to the PDF file.
     * @return string Extracted text, or an "Erreur…" string on failure.
     */
    public function extractTextFromPdf(string $filePath): string
    {
        try {
            // Remote URL (e.g. Cloudinary) → download binary content first
            if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
                $pdfContent = $this->downloadUrl($filePath);

                if ($pdfContent === false || $pdfContent === '') {
                    return "Erreur : impossible de télécharger le CV depuis l'URL : " . $filePath;
                }

                $pdf = $this->parser->parseContent($pdfContent);
            } else {
                // Local path
                $pdf = $this->parser->parseFile($filePath);
            }

            $text = trim($pdf->getText());

            if ($text === '') {
                return "Erreur : le PDF ne contient aucun texte extractible (fichier scanné ou protégé ?).";
            }

            return $text;

        } catch (\Exception $e) {
            return "Erreur lors de l'extraction du texte du PDF : " . $e->getMessage();
        }
    }

    /**
     * OCR via Gemini 2.0 Flash to extract text from an image.
     */
    public function extractTextFromImage(string $imageUrl): string
    {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->geminiApiKey;

        try {
            $response = $this->httpClient->request('GET', $imageUrl);
            if ($response->getStatusCode() !== 200) {
                return "Erreur : impossible de télécharger l'image (" . $response->getStatusCode() . ")";
            }
            $base64Data = base64_encode($response->getContent());

            $cleanUrl = strtok($imageUrl, '?');
            $ext = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));
            $mimeMap = [
                'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png'  => 'image/png', 'webp' => 'image/webp',
                'heic' => 'image/heic', 'heif' => 'image/heif'
            ];
            $mimeType = $mimeMap[$ext] ?? 'image/jpeg';

            $prompt = "Extraire tout le texte lisible de ce CV. Reformater de manière structurée si possible, mais conserver tout le contenu textuel essentiel.";

            $response = $this->httpClient->request('POST', $apiUrl, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Data]]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Erreur : aucun texte extrait par l'IA.";

        } catch (\Exception $e) {
            return "Erreur lors de l'OCR IA : " . $e->getMessage();
        }
    }

    /**
     * Downloads raw bytes from a URL using cURL (preferred) or file_get_contents as fallback.
     */
    private function downloadUrl(string $url): string|false
    {
        // Try cURL first (more reliable, works even if allow_url_fopen is off)
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'SyfonuRH-CVParser/1.0',
            ]);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($content !== false && $httpCode === 200) {
                return $content;
            }
        }

        // Fallback: file_get_contents (requires allow_url_fopen = On)
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 30],
        ]);

        return @file_get_contents($url, false, $context);
    }
}

