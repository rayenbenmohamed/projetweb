<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Parses a natural-language job search query into structured filter criteria.
 *
 * Primary: Groq API (llama-3.3-70b-versatile) — super fast & free.
 * Fallback: local regex heuristics (no API key required).
 */
class JobOffreAiService
{
    private const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL     = 'llama-3.3-70b-versatile';

    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es SyfonuRH AI, un assistant de carrière chaleureux et professionnel.
Ton but est d'aider les utilisateurs à trouver l'emploi de leurs rêves tout en étant agréable à discuter.

L'utilisateur te donne une requête en langage naturel (francais, anglais ou arabe). 
Tu dois faire deux choses :
1. Extraire les filtres techniques dans l'objet "filters".
2. Discuter avec l'utilisateur dans le champ "reply".

Règles de style :
- Sois poli, encourageant et utilise parfois des emojis.
- Si l'utilisateur te salue ou te demande comment tu vas, réponds-lui avant de parler des offres.
- Si l'utilisateur pose une question hors-sujet (cuisine, sport), rappelle-lui gentiment que tu es là pour l'aider dans sa recherche d'emploi.
- Réponds UNIQUEMENT en JSON valide.

Format JSON attendu :
{
  "filters": {
    "q":          string|null,   // mots-clés
    "type":       string|null,   // CDI, CDD, Freelance, Stage, Alternance
    "location":   string|null,   
    "salary_min": number|null,   
    "salary_max": number|null    
  },
  "reply": "Ta réponse personnalisée ici"
}
PROMPT;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $groqApiKey = '',
    ) {}

    /**
     * @return array{filters: array{q: ?string, type: ?string, location: ?string, salary_min: ?float, salary_max: ?float}, reply: string}
     */
    public function parseQuery(string $userMessage): array
    {
        if (!empty($this->groqApiKey)) {
            try {
                return $this->callGroq($userMessage);
            } catch (\Throwable $e) {
                // Fall through to local fallback
            }
        }

        return $this->localFallback($userMessage);
    }

    public function generateDescription(array $data): string
    {
        if (empty($this->groqApiKey)) {
            return "Veuillez configurer une clé API Groq pour utiliser cette fonctionnalité d'IA.";
        }
        
        $prompt = "Tu es un expert RH. Rédige une description de poste professionnelle, complète et attractive pour une offre d'emploi, en te basant sur ces informations :\n" .
                  "- Titre : " . ($data['title'] ?? 'Non précisé') . "\n" .
                  "- Lieu : " . ($data['location'] ?? 'Non précisé') . "\n" .
                  "- Type de contrat : " . ($data['employment_type'] ?? 'Non précisé') . "\n" .
                  "- Salaire : " . ($data['salary'] ?? 'Non précisé') . " €\n" .
                  "- Compétences (Skills) : " . ($data['skills'] ?? 'Non précisé') . "\n" .
                  "- Autres avantages : " . ($data['advantages'] ?? 'Non précisé') . "\n\n" .
                  "Consignes strictes :\n" .
                  "- Ne retourne QUE le texte de l'offre d'emploi.\n" .
                  "- N'ajoute aucune introduction (ex: 'Voici la description...') ni balise Markdown (```).\n" .
                  "- Organise avec des astérisques ou tirets pour rendre ça lisible (Introduction, Vos missions, Le profil attendu, Pourquoi nous rejoindre).\n" .
                  "- Parle à la deuxième personne du pluriel (vous) pour t'adresser au candidat.";

        $response = $this->httpClient->request('POST', self::GROQ_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => self::MODEL,
                'temperature' => 0.6,
                'max_tokens'  => 1500,
                'messages'    => [
                    ['role' => 'system', 'content' => "Tu es un rédacteur RH spécialisé dans la rédaction d'offres d'emploi attractives et engageantes."],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ],
            'timeout' => 20,
        ]);

        $responseData = $response->toArray(false); // don't throw on HTTP Error right away to check structure if needed, though default handles it
        $content = $responseData['choices'][0]['message']['content'] ?? '';
        
        // Strip markdown code if accidentally added
        $content = preg_replace('/^```(?:markdown)?\s*/i', '', trim($content));
        $content = preg_replace('/\s*```$/', '', $content);
        
        return $content;
    }

    // ─── Groq API ────────────────────────────────────────────────────────────

    private function callGroq(string $userMessage): array
    {
        $response = $this->httpClient->request('POST', self::GROQ_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => self::MODEL,
                'temperature' => 0.1,
                'max_tokens'  => 300,
                'messages'    => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user',   'content' => $userMessage],
                ],
            ],
            'timeout' => 10,
        ]);

        $data    = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '{}';

        // Strip potential markdown code fences
        $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
        $content = preg_replace('/\s*```$/',              '', $content);

        $parsed = json_decode($content, true);
        if (!is_array($parsed) || !isset($parsed['filters'])) {
            throw new \RuntimeException('Invalid AI response structure.');
        }

        return $this->sanitize($parsed);
    }

    // ─── Local regex fallback ─────────────────────────────────────────────────

    private function localFallback(string $msg): array
    {
        $lower = mb_strtolower($msg);

        // 1. Handle Small Talk / Greetings
        $greetings = ['bonjour', 'salut', 'hello', 'coucou', 'hey', 'bonsoir', 'salam'];
        foreach ($greetings as $g) {
            if ($lower === $g || $lower === $g . ' !' || $lower === $g . '.') {
                return $this->sanitize([
                    'filters' => ['q' => null, 'type' => null, 'location' => null, 'salary_min' => null, 'salary_max' => null],
                    'reply'   => ucfirst($g) . " ! Je suis SyfonuRH AI. Comment puis-je vous aider dans votre recherche d'emploi aujourd'hui ? 😊",
                ]);
            }
        }

        if (str_contains($lower, 'ca va') || str_contains($lower, 'comment vas-tu') || str_contains($lower, 'comment tu vas')) {
            return $this->sanitize([
                'filters' => ['q' => null, 'type' => null, 'location' => null, 'salary_min' => null, 'salary_max' => null],
                'reply'   => "Je vais très bien, merci de demander ! Je suis prêt à vous aider à dénicher les meilleures opportunités. Que cherchez-vous ? 🚀",
            ]);
        }
        
        if (str_contains($lower, 'qui es-tu') || str_contains($lower, 'c\'est qui') || str_contains($lower, 'ton nom')) {
            return $this->sanitize([
                'filters' => ['q' => null, 'type' => null, 'location' => null, 'salary_min' => null, 'salary_max' => null],
                'reply'   => "Je suis SyfonuRH AI, votre coach de carrière virtuel. Mon super-pouvoir ? Parcourir des centaines d'offres en un clin d'œil pour vous ! ✨",
            ]);
        }

        // 2. Reset intent
        $resetWords = ['réinitialise', 'reset', 'tout voir', 'effacer', 'clear', 'toutes les offres', 'affiche tout'];
        foreach ($resetWords as $w) {
            if (str_contains($lower, $w)) {
                return $this->sanitize([
                    'filters' => ['q' => null, 'type' => null, 'location' => null, 'salary_min' => null, 'salary_max' => null],
                    'reply'   => 'Entendu ! On repart de zéro. Voici toutes les offres disponibles actuellement. 📑',
                ]);
            }
        }

        // Employment type
        $typeMap = [
            'cdi'        => 'CDI',
            'cdd'        => 'CDD',
            'freelance'  => 'Freelance',
            'free-lance' => 'Freelance',
            'stage'      => 'Stage',
            'stagiaire'  => 'Stage',
            'intern'     => 'Stage',
            'alternance' => 'Alternance',
            'alternant'  => 'Alternance',
        ];
        $type = null;
        foreach ($typeMap as $keyword => $value) {
            if (str_contains($lower, $keyword)) {
                $type = $value;
                break;
            }
        }

        // Location Detection
        $location = null;
        
        // 1. Regex with prepositions (more accurate)
        if (preg_match('/(?:à|a|en|dans|at|in)\s+([a-zéàèùâêîôûç\s\-]+?)(?:\s+(?:avec|pour|et|ou|,|$))/ui', $msg, $m)) {
            $candidate = trim($m[1]);
            if (!array_key_exists(mb_strtolower($candidate), $typeMap)) {
                $location = ucwords(mb_strtolower($candidate));
            }
        }
        
        // 2. Keyword-based detection (if no preposition used, like "job sfax")
        if (!$location) {
            $cities = [
                'tunis', 'sfax', 'sousse', 'monastir', 'bizerte', 'gabes', 'kairouan', 'gafsa', 
                'ariana', 'ben arous', 'manouba', 'nabeul', 'zaghouan', 'beja', 'jendouka', 
                'le kef', 'siliana', 'kasserine', 'sidi bouzid', 'mahdia', 'tozeur', 'kebili', 'tataouine', 'mednine'
            ];
            foreach ($cities as $city) {
                if (preg_match('/\b' . preg_quote($city, '/') . '\b/ui', $msg)) {
                    $location = ucwords($city);
                    break;
                }
            }
        }

        // Salary
        $salMin = null;
        $salMax = null;
        // "salaire > 2000", "> 2000", "minimum 1500", "au moins 1000", "plus de 2000"
        if (preg_match('/(?:salaire\s*)?(?:>|supérieur|min(?:imum)?|au moins|plus de|above)\s*(\d[\d\s]*)/ui', $msg, $m)) {
            $salMin = (float) preg_replace('/\s+/', '', $m[1]);
        }
        // "salaire < 3000", "< 3000", "maximum 3000", "au plus 3000", "moins de"
        if (preg_match('/(?:salaire\s*)?(?:<|inférieur|max(?:imum)?|au plus|moins de|below|under)\s*(\d[\d\s]*)/ui', $msg, $m)) {
            $salMax = (float) preg_replace('/\s+/', '', $m[1]);
        }
        // Range "entre 1000 et 3000"
        if (preg_match('/entre\s+(\d[\d\s]*)\s+et\s+(\d[\d\s]*)/ui', $msg, $m)) {
            $salMin = (float) preg_replace('/\s+/', '', $m[1]);
            $salMax = (float) preg_replace('/\s+/', '', $m[2]);
        }

        // Keywords: remove type/location/salary clues, keep the rest as query
        $stop = ['cherche', 'trouve', 'montre', 'affiche', 'recherche', 'offre', 'emploi', 'poste',
                 'job', 'work', 'find', 'show', 'me', 'les', 'des', 'une', 'un', 'avec', 'pour',
                 'salaire', 'salary', 'dt', 'dinar', 'euro', 'eur', 'par mois', 'mensuel'];
        $tokens = preg_split('/\s+/', $lower);
        $kw = array_filter($tokens, fn($t) => !in_array($t, $stop, true) && strlen($t) > 2 && !is_numeric($t));
        // Remove detected type/location from keywords
        if ($type) {
            $kw = array_filter($kw, fn($t) => !str_contains(mb_strtolower($type), $t));
        }
        if ($location) {
            $kw = array_filter($kw, fn($t) => !str_contains(mb_strtolower($location), $t));
        }
        $q = implode(' ', array_values($kw)) ?: null;

        // Build reply
        $parts = [];
        if ($type)   $parts[] = "type **$type**";
        if ($location) $parts[] = "à **$location**";
        if ($salMin) $parts[] = "salaire min **" . number_format($salMin, 0, ',', ' ') . " DT**";
        if ($salMax) $parts[] = "salaire max **" . number_format($salMax, 0, ',', ' ') . " DT**";
        if ($q && !$type && !$location) $parts[] = "mots-clés **$q**";

        $reply = $parts
            ? 'Je filtre les offres ' . implode(', ', $parts) . '.'
            : 'Voici toutes les offres disponibles. Précisez votre recherche si besoin ! 😊';

        return $this->sanitize([
            'filters' => [
                'q'          => $q,
                'type'       => $type,
                'location'   => $location,
                'salary_min' => $salMin,
                'salary_max' => $salMax,
            ],
            'reply' => $reply,
        ]);
    }

    // ─── Sanitize output ─────────────────────────────────────────────────────

    private function sanitize(array $data): array
    {
        $f = $data['filters'] ?? [];

        $validTypes = ['CDI', 'CDD', 'Freelance', 'Stage', 'Alternance'];

        return [
            'filters' => [
                'q'          => isset($f['q'])          && $f['q'] !== ''   ? (string) $f['q']          : null,
                'type'       => isset($f['type'])       && in_array($f['type'], $validTypes, true) ? $f['type'] : null,
                'location'   => isset($f['location'])   && $f['location'] !== '' ? (string) $f['location']  : null,
                'salary_min' => isset($f['salary_min']) && $f['salary_min'] !== null ? (float) $f['salary_min'] : null,
                'salary_max' => isset($f['salary_max']) && $f['salary_max'] !== null ? (float) $f['salary_max'] : null,
            ],
            'reply' => (string) ($data['reply'] ?? 'Voici les résultats correspondant à votre recherche !'),
        ];
    }
}
