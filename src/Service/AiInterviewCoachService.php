<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AiInterviewCoachService
{
    public function __construct(
        #[Autowire('%env(string:AI_CHAT_API_KEY)%')]
        private readonly string $apiKey,
        #[Autowire('%env(string:AI_CHAT_MODEL)%')]
        private readonly string $model,
        #[Autowire('%env(string:AI_CHAT_API_URL)%')]
        private readonly string $apiUrl,
    ) {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function generateReply(string $userMessage, array $history = []): string
    {
        $cleanMessage = trim($userMessage);
        if ($cleanMessage === '') {
            return 'Décris ta situation (poste visé, expérience, blocage) et je te donne un plan concret.';
        }

        if ($this->apiKey === '') {
            return $this->fallbackReply($cleanMessage);
        }

        $messages = [[
            'role' => 'system',
            'content' => "Tu es un coach d'entretien d'embauche et de carrière. Réponds en français, de manière concrète, structurée et bienveillante. Donne des conseils actionnables (questions probables, réponses modèle STAR, points à améliorer CV/profil, négociation, suivi après entretien). Maximum 220 mots.",
        ]];

        $recentHistory = array_slice($history, -8);
        foreach ($recentHistory as $item) {
            if (!isset($item['role'], $item['content'])) {
                continue;
            }
            $role = $item['role'] === 'assistant' ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => (string) $item['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $cleanMessage];

        $payload = json_encode([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.4,
        ], JSON_THROW_ON_ERROR);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n"
                    . 'Authorization: Bearer ' . $this->apiKey . "\r\n",
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]);

        try {
            $response = @file_get_contents($this->apiUrl, false, $context);
            if ($response === false) {
                return $this->fallbackReply($cleanMessage);
            }
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $content = (string) ($decoded['choices'][0]['message']['content'] ?? '');

            return trim($content) !== '' ? trim($content) : $this->fallbackReply($cleanMessage);
        } catch (\Throwable) {
            return $this->fallbackReply($cleanMessage);
        }
    }

    private function fallbackReply(string $message): string
    {
        $m = mb_strtolower($message);

        if (str_contains($m, 'cv')) {
            return "Pour ton CV : 1) Titre clair selon le poste visé, 2) Résultats chiffrés par expérience, 3) Compétences techniques alignées à l'offre, 4) Une version courte (1 page). Envoie-moi le poste cible et je te propose une trame prête à copier.";
        }

        if (str_contains($m, 'salaire') || str_contains($m, 'négociation')) {
            return "Pour négocier : annonce une fourchette (pas un chiffre unique), justifie avec impact + marché, et termine par une ouverture: 'Je suis flexible si le package global est cohérent'. Je peux te rédiger une phrase de négociation adaptée à ton profil.";
        }

        if (str_contains($m, 'question') || str_contains($m, 'entretien')) {
            return "Préparation entretien (méthode STAR) : 1) Situation, 2) Tâche, 3) Action, 4) Résultat. Prépare 3 exemples : réussite, conflit, difficulté. Je peux te faire une simulation de 5 questions avec correction de tes réponses.";
        }

        return "Bonne base. Pour t'aider précisément, donne-moi : poste visé, niveau d'expérience, et ton blocage principal (CV, réponses d'entretien, confiance, négociation). Je te renverrai un plan en étapes courtes.";
    }
}

