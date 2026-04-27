<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\PdfTemplate;

class PdfTemplateService
{
    public function __construct(
        private readonly string $projectDir
    ) {}

    /**
     * Rende le contenu final d'un modèle en remplaçant les placeholders.
     */
    public function renderTemplate(Contract $contract, ?PdfTemplate $template = null): array
    {
        if (!$template) {
            return [
                'header' => '',
                'footer' => '',
                'body' => $contract->getContent() ?? 'Aucun contenu.',
                'primaryColor' => '#2563eb',
                'secondaryColor' => '#64748b',
                'logo' => null,
            ];
        }

        $placeholders = $this->getPlaceholders($contract);

        $body = $this->replacePlaceholders($template->getBodyHtml() ?? '', $placeholders);
        $header = $this->replacePlaceholders($template->getHeaderHtml() ?? '', $placeholders);
        $footer = $this->replacePlaceholders($template->getFooterHtml() ?? '', $placeholders);

        return [
            'header' => $header,
            'footer' => $footer,
            'body' => $body,
            'primaryColor' => $template->getPrimaryColor() ?? '#000000',
            'secondaryColor' => $template->getSecondaryColor() ?? '#6c757d',
            'logo' => $template->getLogoPath(),
        ];
    }

    private function getPlaceholders(Contract $contract): array
    {
        $candidate = $contract->getCandidate();
        $recruiter = $contract->getRecruiter();
        $type = $contract->getTypeContrat();
        $job = $contract->getJobOffre();

        return [
            '{{candidate_name}}' => $candidate ? ($candidate->getFirstName() . ' ' . $candidate->getLastName()) : 'N/A',
            '{{candidate_email}}' => $candidate ? $candidate->getEmail() : 'N/A',
            '{{recruiter_name}}' => $recruiter ? ($recruiter->getFirstName() . ' ' . $recruiter->getLastName()) : 'SyfonuRH',
            '{{salary}}' => number_format($contract->getSalary(), 2, ',', ' '),
            '{{salary_net}}' => number_format($contract->getSalaireNet(), 2, ',', ' '),
            '{{start_date}}' => $contract->getStartDate() ? $contract->getStartDate()->format('d/m/Y') : 'N/A',
            '{{end_date}}' => $contract->getEndDate() ? $contract->getEndDate()->format('d/m/Y') : 'Indéterminée',
            '{{contract_type}}' => $type ? $type->getName() : 'Standard',
            '{{job_title}}' => $job ? $job->getTitle() : 'Poste RH',
            '{{today}}' => date('d/m/Y'),
            '{{contract_id}}' => '#' . str_pad((string) $contract->getId(), 6, '0', STR_PAD_LEFT),
            '{{logo}}' => $contract->getPdfTemplate() && $contract->getPdfTemplate()->getLogoPath() 
                ? '<img src="' . $this->projectDir . '/public' . $contract->getPdfTemplate()->getLogoPath() . '" style="max-height: 60px;">' 
                : '',
            '{{signature}}' => $contract->getSignatureBase64() 
                ? '<img src="' . $contract->getSignatureBase64() . '" style="max-height: 80px; display: block; margin: 10px 0;">' 
                : '<div style="color: #ccc; font-style: italic;">[Signature en attente]</div>',
        ];
    }

    private function replacePlaceholders(string $content, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }
}
