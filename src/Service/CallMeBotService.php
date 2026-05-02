<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Service CallMeBot — envoie des messages WhatsApp automatiques et gratuits.
 *
 * Activation unique requise (30 secondes) :
 *  1. Ajouter +34 644 44 54 05 dans ses contacts WhatsApp
 *  2. Lui envoyer : "I allow callmebot to send me messages"
 *  3. Récupérer le code API reçu par WhatsApp
 *  4. Mettre ce code dans CALLMEBOT_API_KEY du .env
 */
class CallMeBotService
{
    private const API_URL = 'https://api.callmebot.com/whatsapp.php';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $callMeBotApiKey = ''
    ) {}

    /**
     * Envoie un message WhatsApp via CallMeBot.
     *
     * @param string $phone   Numéro au format international sans + (ex: 21625436090)
     * @param string $message Texte du message
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->callMeBotApiKey) || $this->callMeBotApiKey === 'VOTRE_CLE_API') {
            $this->logger->warning('[CallMeBot] API key non configurée. Ajoutez CALLMEBOT_API_KEY dans .env');
            return false;
        }

        // Nettoyer le numéro : uniquement des chiffres, sans le +
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);

        // Ajouter indicatif tunisien si 8 chiffres locaux
        if (strlen($cleanPhone) === 8) {
            $cleanPhone = '216' . $cleanPhone;
        }

        if (empty($cleanPhone)) {
            $this->logger->warning('[CallMeBot] Numéro invalide : ' . $phone);
            return false;
        }

        $url = self::API_URL . '?' . http_build_query([
            'phone'  => $cleanPhone,
            'text'   => $message,
            'apikey' => $this->callMeBotApiKey,
        ]);

        $context = stream_context_create([
            'http' => [
                'timeout'        => 2, // Timeout réduit à 2 secondes pour ne pas bloquer
                'ignore_errors'  => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        try {
            $response = file_get_contents($url, false, $context);
            $httpCode = 0;

            // Récupérer le code HTTP de la réponse
            if (isset($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $header, $m)) {
                        $httpCode = (int) $m[1];
                    }
                }
            }

            if ($httpCode === 200) {
                $this->logger->info('[CallMeBot] Message WhatsApp envoyé à +' . $cleanPhone);
                return true;
            }

            $this->logger->error('[CallMeBot] Erreur HTTP ' . $httpCode . ' — Réponse: ' . $response);
            return false;

        } catch (\Exception $e) {
            $this->logger->error('[CallMeBot] Exception : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie la notification de nouvelle candidature au recruteur.
     */
    public function notifyNewApplication(
        string $recruiterPhone,
        string $candidatName,
        string $offerTitle,
        string $companyName
    ): bool {
        $message = "🔔 Nouvelle candidature reçue !\n"
            . "📋 Offre : {$offerTitle}\n"
            . "👤 Candidat : {$candidatName}\n"
            . "🏢 Entreprise : {$companyName}\n"
            . "Connectez-vous à votre espace recruteur pour consulter les détails.";

        return $this->send($recruiterPhone, $message);
    }
}
