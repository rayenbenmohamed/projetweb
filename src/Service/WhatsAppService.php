<?php

namespace App\Service;

use App\Entity\Contract;

/**
 * WhatsApp Business Cloud API Service (Meta).
 *
 * Uses PHP's native cURL so no extra Symfony package is needed.
 *
 * Free tier: 1 000 conversations / month.
 * Setup guide: https://developers.facebook.com/docs/whatsapp/cloud-api/get-started
 *
 * Required .env variables:
 *   WHATSAPP_ACCESS_TOKEN=  (your permanent token from Meta developer console)
 *   WHATSAPP_PHONE_NUMBER_ID=  (numeric Phone Number ID, not the display number)
 */
class WhatsAppService
{
    private const GRAPH_API = 'https://graph.facebook.com/v20.0';

    public function __construct(
        private readonly string $accessToken,
        private readonly string $phoneNumberId,
    ) {}

    /**
     * Uploads the PDF bytes to the WhatsApp Media endpoint,
     * then sends it as a document to the given phone number.
     *
     * @param Contract $contract    The contract entity
     * @param string   $pdfContent  Raw PDF binary content
     * @param string   $toPhone     Candidate phone (any format, will be normalized)
     */
    public function sendContractPdf(Contract $contract, string $pdfContent, string $toPhone): array
    {
        // ── Guard: credentials not yet set ─────────────────────────────────
        if (empty($this->accessToken) || str_contains($this->accessToken, 'your_')) {
            return [
                'success' => false,
                'error'   => 'WhatsApp API non configuré. Renseignez WHATSAPP_ACCESS_TOKEN et WHATSAPP_PHONE_NUMBER_ID dans votre fichier .env.',
            ];
        }

        // ── 1. Normalize to E.164 without leading + ────────────────────────
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $toPhone);
        if (strlen($phone) === 8) {
            $phone = '216' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '216' . substr($phone, 1);
        }

        // ── 2. Upload PDF as WhatsApp media ───────────────────────────────
        $filename = sprintf('Contrat-SyfonuRH-%d.pdf', $contract->getId());
        $uploadResult = $this->uploadMedia($pdfContent, $filename);

        if (!isset($uploadResult['id'])) {
            $apiError = $uploadResult['error']['message'] ?? json_encode($uploadResult);
            return ['success' => false, 'error' => 'Upload PDF échoué : ' . $apiError];
        }

        $mediaId = $uploadResult['id'];

        // ── 3. Build caption ───────────────────────────────────────────────
        $candidateName = $contract->getCandidate()?->getFirstName() ?? 'Madame/Monsieur';
        $caption = implode("\n", [
            '📄 *Contrat Professionnel — SyfonuRH*',
            '',
            "Bonjour {$candidateName},",
            '',
            'Veuillez trouver ci-joint votre contrat professionnel en format PDF.',
            'Merci de le consulter et de nous revenir pour toute question.',
            '',
            '_Cordialement,_',
            "_L'équipe RH SyfonuRH_",
        ]);

        // ── 4. Send document message ───────────────────────────────────────
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'document',
            'document'          => [
                'id'       => $mediaId,
                'filename' => $filename,
                'caption'  => $caption,
            ],
        ];

        $result = $this->post('/messages', $body);

        if (isset($result['messages'][0]['id'])) {
            return ['success' => true, 'messageId' => $result['messages'][0]['id']];
        }

        return [
            'success' => false,
            'error'   => $result['error']['message'] ?? json_encode($result),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Uploads binary content as a WhatsApp media file.
     * Returns the full API response array (contains 'id' on success, 'error' on failure).
     */
    private function uploadMedia(string $content, string $filename): array
    {
        $url = self::GRAPH_API . '/' . $this->phoneNumberId . '/media';

        $tmpFile = tempnam(sys_get_temp_dir(), 'whatsapp_pdf_');
        file_put_contents($tmpFile, $content);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->accessToken,
                ],
                CURLOPT_POSTFIELDS     => [
                    'messaging_product' => 'whatsapp',
                    'type'              => 'application/pdf',
                    'file'              => new \CURLFile($tmpFile, 'application/pdf', $filename),
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response ?: '{}', true) ?? [];

            // Log for debugging in dev
            if ($httpCode !== 200) {
                error_log('[WhatsApp] Media upload failed (' . $httpCode . '): ' . $response);
            }

            return $data;
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * Posts a JSON payload to the WhatsApp Graph API.
     */
    private function post(string $endpoint, array $payload): array
    {
        $url = self::GRAPH_API . '/' . $this->phoneNumberId . $endpoint;
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response ?: '{}', true) ?? [];
    }
}
