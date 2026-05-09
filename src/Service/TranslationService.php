<?php

namespace App\Service;

class TranslationService
{
    public function translateToEnglish(string $text): array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return [
                'translatedText' => '',
                'source' => 'none',
                'error' => null,
            ];
        }

        // Reuse the same key you already configured.
        $apiKey = $this->getEnvString('XAI_API_KEY');
        if (!$apiKey || !function_exists('curl_init')) {
            return [
                'translatedText' => $trimmed,
                'source' => 'fallback',
                'error' => 'Translation provider unavailable',
            ];
        }

        $endpoint = str_starts_with($apiKey, 'gsk_')
            ? 'https://api.groq.com/openai/v1/chat/completions'
            : 'https://api.x.ai/v1/chat/completions';

        $model = str_starts_with($apiKey, 'gsk_')
            ? $this->getEnvString('GROQ_MODEL', 'llama-3.1-8b-instant')
            : $this->getEnvString('XAI_MODEL', 'grok-2-latest');

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Translate user text to natural English only. Keep original meaning. Output only translated text.',
                ],
                [
                    'role' => 'user',
                    'content' => $trimmed,
                ],
            ],
            'temperature' => 0.1,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if ($this->getEnvString('AI_INSECURE_SSL', '1') === '1') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$response || $status >= 400) {
            return [
                'translatedText' => $trimmed,
                'source' => 'fallback',
                'error' => sprintf('status=%s curl_error=%s', (string) $status, $curlError ?: 'none'),
            ];
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;
        $translated = is_string($content) ? trim($content) : '';

        if ($translated === '') {
            return [
                'translatedText' => $trimmed,
                'source' => 'fallback',
                'error' => 'Empty translation response',
            ];
        }

        return [
            'translatedText' => $translated,
            'source' => str_starts_with($apiKey, 'gsk_') ? 'groq' : 'xai',
            'error' => null,
        ];
    }

    private function getEnvString(string $key, ?string $default = null): ?string
    {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || trim($value) === '') {
            return $default;
        }

        $trimmed = trim($value);
        return $trimmed !== '' ? $trimmed : $default;
    }
}
