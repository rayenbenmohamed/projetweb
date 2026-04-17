<?php

namespace App\Service;

/**
 * Service de scoring manuel universel basé sur un système de points.
 * Répartition : Technique (50), Expérience (25), Langues (15), Bonus (10).
 */
class CVKeywordScorerService
{
    private const STOP_WORDS = [
        'le','la','les','de','du','des','un','une','et','en','au','aux',
        'pour','par','sur','avec','dans','est','sont','être','avoir','vous',
        'nous','il','elle','ils','elles','se','si','qui','que','qu','ou',
        'mais','donc','car','ni','or','à','y','ce','cet','cette','ces',
        'mon','ton','son','ma','ta','sa','nos','vos','leurs','leur',
        'the','a','an','of','in','to','and','or','for','with'
    ];

    private const LANGUAGES = [
        'français', 'french', 'anglais', 'english', 'arabe', 'arabic',
        'allemand', 'german', 'espagnol', 'spanish', 'italien', 'italian'
    ];

    private const EXPERIENCE_TERMS = [
        'expérience', 'experience', 'stage', 'internship', 'alternance',
        'pfe', 'cdi', 'cdd', 'freelance', 'professionnel', 'professional',
        'diplôme', 'degree', 'master', 'licence', 'baccalauréat', 'formation',
        'education', 'certificat', 'certification'
    ];

    private const BONUS_TERMS = [
        'linkedin', 'github', 'behance', 'portfolio', 'git', 'docker',
        'agile', 'scrum', 'office', 'excel', 'word', 'communication'
    ];

    public function analyze(string $jobDescription, string $cvText): array
    {
        $cvTextLower = mb_strtolower($cvText);
        $jobKeywords = $this->extractKeywords($jobDescription);
        
        $scores = [
            'technical'  => 0,
            'experience' => 0,
            'languages'  => 0,
            'bonus'      => 0
        ];

        $foundKeywords = [];
        $foundLanguages = [];

        // 1. Technique (Max 50 pts)
        if (!empty($jobKeywords)) {
            $matchedCount = 0;
            foreach ($jobKeywords as $kw) {
                // Utilisation de stripos pour plus de souplesse (détection des sous-chaines)
                if (mb_stripos($cvTextLower, $kw) !== false) {
                    $matchedCount++;
                    $foundKeywords[] = $kw;
                }
            }
            $scores['technical'] = (int) min(50, ($matchedCount / count($jobKeywords)) * 50);
        }

        // 2. Expérience & Études (Max 25 pts)
        $expCount = 0;
        foreach (self::EXPERIENCE_TERMS as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/ui', $cvTextLower)) {
                $expCount++;
            }
        }
        $scores['experience'] = (int) min(25, $expCount * 4);

        // 3. Langues (Max 15 pts)
        foreach (self::LANGUAGES as $lang) {
            if (preg_match('/\b' . preg_quote($lang, '/') . '\b/ui', $cvTextLower)) {
                if (!in_array($lang, $foundLanguages)) {
                    $scores['languages'] += 5;
                    $foundLanguages[] = $lang;
                }
            }
        }
        $scores['languages'] = min(15, $scores['languages']);

        // 4. Bonus (Max 10 pts)
        $bonusCount = 0;
        foreach (self::BONUS_TERMS as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/ui', $cvTextLower)) {
                $bonusCount++;
            }
        }
        $scores['bonus'] = (int) min(10, $bonusCount * 2);

        // Score Final
        $finalScore = array_sum($scores);

        return [
            'score'            => $finalScore,
            'summary'          => $this->generateSummary($finalScore, $foundKeywords, $foundLanguages),
            'recommendation'   => $this->generateRecommendation($finalScore),
            'found_keywords'   => $foundKeywords,
            'missing_keywords' => array_diff($jobKeywords, $foundKeywords),
            'raw_text_analyzed' => mb_substr($cvText, 0, 4000),
            'details'          => $scores // On garde les détails pour affichage éventuel
        ];
    }

    private function extractKeywords(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s\-+#\.]/u', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $keywords = [];
        foreach ($words as $word) {
            // Autoriser les technos courtes (JS, C#, Go, AI) - min 2 caractères
            if (mb_strlen($word) >= 2 && !in_array($word, self::STOP_WORDS)) {
                $keywords[] = $word;
            }
        }
        return array_slice(array_unique($keywords), 0, 30);
    }

    private function generateSummary(int $score, array $kw, array $langs): string
    {
        $res = "Analyse manuelle terminée. Score global : $score/100. ";
        $res .= count($kw) . " compétences clés détectées. ";
        if (!empty($langs)) {
            $res .= "Langues identifiées : " . implode(', ', array_unique($langs)) . ".";
        }
        return $res;
    }

    private function generateRecommendation(int $score): string
    {
        if ($score >= 75) return "Profil hautement qualifié pour ce poste.";
        if ($score >= 50) return "Profil intéressant avec de bonnes bases.";
        if ($score >= 30) return "Profil junior ou nécessitant une formation complémentaire.";
        return "Profil peu en adéquation avec les exigences du poste.";
    }
}
