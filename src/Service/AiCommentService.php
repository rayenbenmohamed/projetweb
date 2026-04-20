<?php

namespace App\Service;

use App\Entity\ForumPost;

class AiCommentService
{
    private string $lastSource = 'fallback';
    private ?string $lastError = null;

    public function generateCommentSuggestion(ForumPost $post, ?string $userPrompt = null): string
    {
        $this->lastSource = 'fallback';
        $this->lastError = null;

        if (!function_exists('curl_init')) {
            return $this->buildFallbackSuggestion($post, $userPrompt);
        }

        $systemPrompt = 'You write respectful, concise forum replies. Keep answer between 1-3 sentences.';
        $userMessage = sprintf(
            "Post title: %s\nPost content: %s\nUser request: %s\nWrite a comment suggestion.",
            $post->getTitle(),
            $post->getContent(),
            $userPrompt ?: 'No specific style requested'
        );

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $xaiApiKey = $this->getEnvString('XAI_API_KEY');
        if ($xaiApiKey) {
            // gsk_ keys are usually Groq API keys (OpenAI-compatible endpoint).
            if (str_starts_with($xaiApiKey, 'gsk_')) {
                $groqModel = $this->getEnvString('GROQ_MODEL', 'llama-3.1-8b-instant');
                $groqReply = $this->requestChatCompletion(
                    'https://api.groq.com/openai/v1/chat/completions',
                    $xaiApiKey,
                    $groqModel,
                    $messages
                );
                if ($groqReply) {
                    $this->lastSource = 'groq';
                    return $groqReply;
                }
            } else {
                $xaiModel = $this->getEnvString('XAI_MODEL', 'grok-2-latest');
                $xaiReply = $this->requestChatCompletion(
                    'https://api.x.ai/v1/chat/completions',
                    $xaiApiKey,
                    $xaiModel,
                    $messages
                );
                if ($xaiReply) {
                    $this->lastSource = 'xai';
                    return $xaiReply;
                }
            }
        }

        // Optional fallback provider if xAI key is not configured.
        $openAiApiKey = $this->getEnvString('OPENAI_API_KEY');
        if ($openAiApiKey) {
            $openAiReply = $this->requestChatCompletion(
                'https://api.openai.com/v1/chat/completions',
                $openAiApiKey,
                'gpt-4o-mini',
                $messages
            );
            if ($openAiReply) {
                $this->lastSource = 'openai';
                return $openAiReply;
            }
        }

        if ($this->lastError === null) {
            $this->lastError = 'No API key configured. Set XAI_API_KEY in .env.local.';
        }

        return $this->buildFallbackSuggestion($post, $userPrompt);
    }

    public function getLastSource(): string
    {
        return $this->lastSource;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function buildFallbackSuggestion(ForumPost $post, ?string $userPrompt): string
    {
        $title = trim((string) $post->getTitle());
        $body = trim(strip_tags((string) $post->getContent()));
        $snippet = mb_substr($body, 0, 110);
        $seed = crc32(($title ?: 'post') . '|' . ($userPrompt ?: '') . '|' . $snippet);

        $openers = [
            'Interesting perspective on "%s".',
            'You raised a solid point with "%s".',
            'Great topic: "%s".',
            'This post about "%s" is worth discussing.',
        ];
        $followUps = [
            'One thing I would add is to include a practical example so others can compare experiences.',
            'I agree with the direction and would love to hear how people handled this in real situations.',
            'A next step could be clarifying the main challenge so the discussion becomes more actionable.',
            'It might help to highlight one concrete case so people can give more targeted feedback.',
        ];

        $opener = $openers[$seed % count($openers)];
        $followUp = $followUps[$seed % count($followUps)];
        $promptLine = $userPrompt ? ' Focus requested: ' . $userPrompt . '.' : '';

        return sprintf($opener, $title ?: 'this subject') . ' ' . $followUp .
            ($snippet !== '' ? ' Context noted: "' . $snippet . (mb_strlen($body) > 110 ? '...' : '') . '".' : '') .
            $promptLine;
    }

    private function requestChatCompletion(
        string $endpoint,
        string $apiKey,
        string $model,
        array $messages
    ): ?string
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        if ($this->getEnvString('AI_INSECURE_SSL', '1') === '1') {
            // Local Windows fix when CA bundle is not configured.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$response || $status >= 400) {
            $snippet = $response ? mb_substr($response, 0, 300) : 'No response body';
            $this->lastError = sprintf(
                'endpoint=%s status=%s curl_error=%s response=%s',
                $endpoint,
                (string) $status,
                $curlError ?: 'none',
                $snippet
            );
            error_log('[AiCommentService] Provider call failed ' . $this->lastError);
            return null;
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        return is_string($content) && trim($content) !== '' ? trim($content) : null;
    }

    private function getEnvString(string $key, ?string $default = null): ?string
    {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || trim($value) === '') {
            $value = $this->readFromDotEnvFile($key);
        }

        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        return $trimmed !== '' ? $trimmed : $default;
    }

    private function readFromDotEnvFile(string $key): ?string
    {
        $projectDir = dirname(__DIR__, 2);
        $envFiles = [
            $projectDir . '/.env.local',
            $projectDir . '/.env.dev.local',
            $projectDir . '/.env',
        ];

        foreach ($envFiles as $envPath) {
            $value = $this->readKeyFromSingleEnvFile($envPath, $key);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function readKeyFromSingleEnvFile(string $envPath, string $key): ?string
    {
        if (!is_file($envPath) || !is_readable($envPath)) {
            return null;
        }

        $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return null;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_starts_with($line, $key . '=')) {
                continue;
            }

            $raw = substr($line, strlen($key) + 1);
            $raw = trim($raw);
            if ($raw === '') {
                return null;
            }

            // Strip optional surrounding quotes.
            if ((str_starts_with($raw, '"') && str_ends_with($raw, '"')) ||
                (str_starts_with($raw, "'") && str_ends_with($raw, "'"))) {
                $raw = substr($raw, 1, -1);
            }

            return $raw;
        }

        return null;
    }
}
