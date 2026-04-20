<?php

// Mocking required Symfony/HttpClient components to run standalone
namespace Symfony\Contracts\HttpClient {
    interface HttpClientInterface {
        public function request(string $method, string $url, array $options = []);
    }
}

namespace App\Service {
    // Re-include the service logic (copy-pasted for test purposes)
    class JobOffreAiService {
        private const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';
        private const MODEL     = 'llama-3.3-70b-versatile';

        public function __construct(
            private $httpClient = null,
            private string $groqApiKey = '',
        ) {}

        public function parseQuery(string $userMessage): array {
            // Force local fallback for this test since we have no API key
            return $this->localFallback($userMessage);
        }

        private function localFallback(string $msg): array {
            $lower = mb_strtolower($msg);
            $resetWords = ['réinitialise', 'reset', 'tout voir', 'effacer', 'clear', 'toutes les offres', 'affiche tout'];
            foreach ($resetWords as $w) {
                if (str_contains($lower, $w)) {
                    return $this->sanitize([
                        'filters' => ['q' => null, 'type' => null, 'location' => null, 'salary_min' => null, 'salary_max' => null],
                        'reply'   => 'Filtres réinitialisés ! Voici toutes les offres disponibles. 🎉',
                    ]);
                }
            }

            $typeMap = [
                'cdi'        => 'CDI',
                'cdd'        => 'CDD',
                'freelance'  => 'Freelance',
                'stage'      => 'Stage',
                'alternance' => 'Alternance',
            ];
            $type = null;
            foreach ($typeMap as $keyword => $value) {
                if (str_contains($lower, $keyword)) {
                    $type = $value;
                    break;
                }
            }

            $location = null;
            if (preg_match('/(?:à|a|en|dans|at|in)\s+([a-zéàèùâêîôûç\s\-]+?)(?:\s+(?:avec|pour|et|ou|,|$))/ui', $msg, $m)) {
                $candidate = trim($m[1]);
                if (!array_key_exists(mb_strtolower($candidate), $typeMap)) {
                    $location = ucwords(mb_strtolower($candidate));
                }
            }

            $salMin = null;
            $salMax = null;
            if (preg_match('/(?:salaire\s*)?(?:>|supérieur|min(?:imum)?|au moins|plus de|above)\s*(\d[\d\s]*)/ui', $msg, $m)) {
                $salMin = (float) preg_replace('/\s+/', '', $m[1]);
            }
            if (preg_match('/(?:salaire\s*)?(?:<|inférieur|max(?:imum)?|au plus|moins de|below|under)\s*(\d[\d\s]*)/ui', $msg, $m)) {
                $salMax = (float) preg_replace('/\s+/', '', $m[1]);
            }

            $q = $msg; // Simplified keywords for test
            
            $parts = [];
            if ($type)   $parts[] = "type **$type**";
            if ($location) $parts[] = "à **$location**";
            if ($salMin) $parts[] = "salaire min **" . number_format($salMin, 0, ',', ' ') . " DT**";
            
            $reply = $parts ? 'Je filtre les offres ' . implode(', ', $parts) . '.' : 'Voici les résultats.';

            return $this->sanitize([
                'filters' => ['q' => null, 'type' => $type, 'location' => $location, 'salary_min' => $salMin, 'salary_max' => $salMax],
                'reply' => $reply
            ]);
        }

        private function sanitize(array $data): array {
            $f = $data['filters'];
            return [
                'filters' => [
                    'q'          => $f['q'] ?? null,
                    'type'       => $f['type'] ?? null,
                    'location'   => $f['location'] ?? null,
                    'salary_min' => $f['salary_min'] ?? null,
                    'salary_max' => $f['salary_max'] ?? null,
                ],
                'reply' => $data['reply']
            ];
        }
    }
}

namespace {
    use App\Service\JobOffreAiService;

    $service = new JobOffreAiService();
    
    $tests = [
        "Je cherche un CDI à Tunis",
        "Offres de stage en France avec salaire > 1000",
        "Montre-moi les jobs Freelance",
        "Réinitialise les filtres"
    ];

    echo "--- Test Assistant AI (Mode Fallback Local) ---\n\n";

    foreach ($tests as $q) {
        echo "LOG: Utilisateur: \"$q\"\n";
        $res = $service->parseQuery($q);
        echo "LOG: Réponse AI: " . $res['reply'] . "\n";
        echo "LOG: Filtres extraits: " . json_encode($res['filters'], JSON_PRETTY_PRINT) . "\n";
        echo "-------------------------------------------\n";
    }
}
