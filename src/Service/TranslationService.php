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

        // Simple mock translation
        $translated = $this->simpleTranslate($trimmed);
        if ($translated !== $trimmed) {
            return [
                'translatedText' => $translated,
                'source' => 'mock',
                'error' => null,
            ];
        }

        // Fallback to original
        return [
            'translatedText' => $trimmed,
            'source' => 'fallback',
            'error' => 'Translation not available',
        ];
    }

    private function simpleTranslate(string $text): string
    {
        $translations = [
            'bonjour' => 'hello',
            'le monde' => 'the world',
            'comment allez-vous' => 'how are you',
            'merci' => 'thank you',
            'au revoir' => 'goodbye',
            'oui' => 'yes',
            'non' => 'no',
            'je' => 'I',
            'tu' => 'you',
            'il' => 'he',
            'elle' => 'she',
            'nous' => 'we',
            'vous' => 'you',
            'ils' => 'they',
            'elles' => 'they',
            'est' => 'is',
            'sont' => 'are',
            'a' => 'has',
            'ont' => 'have',
            'un' => 'a',
            'une' => 'a',
            'le' => 'the',
            'la' => 'the',
            'les' => 'the',
            'de' => 'of',
            'du' => 'of the',
            'des' => 'of the',
            'et' => 'and',
            'à' => 'to',
            'dans' => 'in',
            'sur' => 'on',
            'avec' => 'with',
            'pour' => 'for',
            'par' => 'by',
            'sans' => 'without',
            'sous' => 'under',
            'entre' => 'between',
            'contre' => 'against',
            'chez' => 'at',
            'pendant' => 'during',
            'depuis' => 'since',
            'jusque' => 'until',
            'lorsque' => 'when',
            'si' => 'if',
            'quand' => 'when',
            'où' => 'where',
            'comment' => 'how',
            'pourquoi' => 'why',
            'quel' => 'which',
            'quelle' => 'which',
            'quels' => 'which',
            'quelles' => 'which',
            'ce' => 'this',
            'cet' => 'this',
            'cette' => 'this',
            'ces' => 'these',
            'mon' => 'my',
            'ma' => 'my',
            'mes' => 'my',
            'ton' => 'your',
            'ta' => 'your',
            'tes' => 'your',
            'son' => 'his/her',
            'sa' => 'his/her',
            'ses' => 'his/her',
            'notre' => 'our',
            'nos' => 'our',
            'votre' => 'your',
            'vos' => 'your',
            'leur' => 'their',
            'leurs' => 'their',
        ];

        $lower = strtolower($text);
        $words = explode(' ', $lower);
        $translatedWords = [];
        foreach ($words as $word) {
            $clean = preg_replace('/[^\w]/', '', $word);
            $translatedWords[] = $translations[$clean] ?? $word;
        }
        return implode(' ', $translatedWords);
    }
}
