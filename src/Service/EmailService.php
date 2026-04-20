<?php

namespace App\Service;

use App\Entity\Contract;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly PdfGeneratorService $pdfGenerator
    ) {}

    /**
     * Génère et envoie le contrat automatiquement par e-mail au candidat.
     *
     * @param Contract $contract L'entité contrat
     * @return array Résultat de l'opération
     */
    public function sendContractEmailAutomatically(Contract $contract): array
    {
        $pdfContent = $this->pdfGenerator->generateContractPdf($contract);
        return $this->sendContractPdf($contract, $pdfContent);
    }

    /**
     * Envoie le contrat PDF par e-mail au candidat.
     *
     * @param Contract $contract   L'entité contrat
     * @param string   $pdfContent Le contenu binaire du PDF
     */
    public function sendContractPdf(Contract $contract, string $pdfContent): array
    {
        $candidate = $contract->getCandidate();
        if (!$candidate || !$candidate->getEmail()) {
            return [
                'success' => false,
                'error'   => 'Le candidat n\'a pas d\'adresse e-mail enregistrée.',
            ];
        }

        $filename = sprintf('contrat-%d.pdf', $contract->getId());

        try {
            $email = (new Email())
                ->from(new Address('oussemabelhajsghair@gmail.com', 'SyfonuRH Recruiting'))
                ->to($candidate->getEmail())
                ->subject('Votre contrat SyfonuRH — Action Requise')
                ->html($this->getEmailBody($contract))
                ->attach($pdfContent, $filename, 'application/pdf');

            $this->mailer->send($email);

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => 'Échec de l\'envoi de l\'e-mail : ' . $e->getMessage(),
            ];
        }
    }

    private function getEmailBody(Contract $contract): string
    {
        $name = $contract->getCandidate() ? $contract->getCandidate()->getFirstName() : 'Candidat';
        $type = $contract->getTypeContrat() ? $contract->getTypeContrat()->getName() : 'Standard';

        return "
            <div style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #2563eb;'>Félicitations !</h2>
                <p>Bonjour <strong>{$name}</strong>,</p>
                <p>Nous sommes ravis de vous accueillir au sein de notre équipe.</p>
                <p>Veuillez trouver ci-joint votre contrat de type <strong>{$type}</strong> pour signature.</p>
                <p>Une fois le document consulté, vous pouvez le signer directement via votre portail candidat.</p>
                <br>
                <p>Cordialement,</p>
                <p><strong>L'équipe RH SyfonuRH</strong></p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 0.8rem; color: #777;'>Ceci est un message automatique, merci de ne pas y répondre directement.</p>
            </div>
        ";
    }
}
