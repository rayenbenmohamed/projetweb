<?php

namespace App\Service;

class BadWordModerationService
{
    /**
     * Keep this list short and explicit for demo purposes.
     * You can extend it or load from DB/file later.
     */
    private array $blockedWords = [
        'idiot',
        'stupid',
        'dumb',
        'hate',
        'trash',
        'shit',
        'fuck',
        'bitch',
        'asshole',
    ];

    public function detect(string $content): array
    {
        $normalizedContent = mb_strtolower($content);
        $foundWords = [];

        foreach ($this->blockedWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $normalizedContent)) {
                $foundWords[] = $word;
            }
        }

        $sanitized = $content;
        foreach ($foundWords as $word) {
            $sanitized = preg_replace(
                '/\b' . preg_quote($word, '/') . '\b/i',
                str_repeat('*', strlen($word)),
                $sanitized
            );
        }

        return [
            'containsBadWords' => !empty($foundWords),
            'badWords' => array_values(array_unique($foundWords)),
            'sanitizedText' => $sanitized,
        ];
    }
}
