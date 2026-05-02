<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

/**
 * Service pour envoyer des notifications WhatsApp via Twilio
 * lorsqu'un candidat postule à une offre d'emploi.
 */
class TwilioWhatsAppService
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber; // ex: whatsapp:+14155238886 (sandbox Twilio)

    public function __construct(
        string $twilioAccountSid,
        string $twilioAuthToken,
        string $twilioWhatsappFrom,
        private readonly LoggerInterface $logger
    ) {
        $this->accountSid = $twilioAccountSid;
        $this->authToken  = $twilioAuthToken;
        $this->fromNumber = $twilioWhatsappFrom;
    }

    /**
     * Envoie un message WhatsApp à l'entreprise lorsqu'un candidat postule.
     *
     * @param string $toPhone   Numéro de téléphone de l'entreprise (ex: +21612345678)
     * @param string $candidatName  Nom complet du candidat
     * @param string $offerTitle    Titre de l'offre d'emploi
     * @param string $entrepriseName Nom de l'entreprise
     * @return bool  true si envoyé avec succès
     */
    public function sendApplicationNotification(
        string $toPhone,
        string $candidatName,
        string $offerTitle,
        string $entrepriseName
    ): bool {
        // Vérifier que les credentials sont configurés
        if (empty($this->accountSid) || $this->accountSid === 'your_twilio_account_sid') {
            $this->logger->warning('[Twilio] Credentials non configurés. Message WhatsApp non envoyé.');
            return false;
        }

        // Nettoyer et formater le numéro de téléphone
        $cleanPhone = $this->formatPhoneNumber($toPhone);
        if (!$cleanPhone) {
            $this->logger->warning('[Twilio] Numéro de téléphone invalide : ' . $toPhone);
            return false;
        }

        $message = "🔔 *Nouvelle candidature reçue !*\n\n"
            . "📋 *Offre :* {$offerTitle}\n"
            . "👤 *Candidat :* {$candidatName}\n"
            . "🏢 *Entreprise :* {$entrepriseName}\n\n"
            . "Connectez-vous à votre espace recruteur pour consulter les détails de cette candidature.";

        try {
            $client = new Client($this->accountSid, $this->authToken);

            $client->messages->create(
                'whatsapp:' . $cleanPhone,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );

            $this->logger->info('[Twilio] Notification WhatsApp envoyée à ' . $cleanPhone . ' pour l\'offre : ' . $offerTitle);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('[Twilio] Erreur envoi WhatsApp : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Nettoie et formate un numéro de téléphone au format international E.164.
     * Exemples : "0612345678" → "+33612345678" / "+21698765432" → "+21698765432"
     */
    private function formatPhoneNumber(string $phone): ?string
    {
        // Supprimer tous les caractères non numériques sauf le +
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if (empty($cleaned)) {
            return null;
        }

        // Déjà au format international (commence par +)
        if (str_starts_with($cleaned, '+')) {
            return strlen($cleaned) >= 8 ? $cleaned : null;
        }

        // Numéro tunisien local (commence par 0 ou directement les 8 chiffres)
        if (str_starts_with($cleaned, '00')) {
            return '+' . substr($cleaned, 2);
        }

        // Si le numéro fait 8 chiffres → numéro tunisien sans indicatif
        if (strlen($cleaned) === 8) {
            return '+216' . $cleaned;
        }

        // Si le numéro commence par 0 et fait 9-10 chiffres → France ou autre pays
        if (str_starts_with($cleaned, '0') && strlen($cleaned) >= 9) {
            return '+33' . substr($cleaned, 1);
        }

        // Ajouter + si numéro long (indicatif probable)
        if (strlen($cleaned) >= 10) {
            return '+' . $cleaned;
        }

        return null;
    }
}
