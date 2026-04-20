<?php

namespace App\Service;

use App\Entity\Contract;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGeneratorService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly PdfTemplateService $templateService,
        private readonly string $projectDir
    ) {}

    /**
     * Génère le contenu binaire du PDF pour un contrat donné.
     *
     * @param Contract $contract
     * @return string
     */
    public function generateContractPdf(Contract $contract): string
    {
        $template = $contract->getPdfTemplate();

        if ($template) {
            $templateData = $this->templateService->renderTemplate($contract, $template);
            $html = $this->twig->render('contract/pdf_dynamic.html.twig', [
                'contract'     => $contract,
                'templateData' => $templateData,
                'public_dir'   => $this->projectDir . '/public',
            ]);
        } else {
            $html = $this->twig->render('contract/pdf.html.twig', [
                'contract'     => $contract,
                'gd_installed' => extension_loaded('gd'),
            ]);
        }

        $gdInstalled = extension_loaded('gd');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        // ── SAFEGUARD: If GD is missing, disable PNG alpha/images to prevent fatal error ──
        if (!$gdInstalled) {
            // Stripping images from HTML if GD is missing to prevent dompdf exception
            $html = preg_replace('/<img[^>]+>/i', '<div style="background-color:#f8f9fa; border:1px solid #dee2e6; color:#a0aec0; padding:15px; text-align:center; border-radius:5px; font-size:11px; margin:10px 0;">[ Signature ou Logo indisponible - Extension GD requise ]</div>', $html);
        }

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return $dompdf->output();
    }
}
