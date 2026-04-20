<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Service\PdfTemplateService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contract')]
class ContractPdfController extends AbstractController
{
    private $templateService;

    public function __construct(PdfTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Télécharge le PDF du contrat (force le download).
     */
    #[Route('/{id}/export/pdf/{filename}', name: 'app_contract_pdf', defaults: ['filename' => 'contrat.pdf'], methods: ['GET'])]
    public function downloadPdf(Contract $contract): Response
    {
        $template = $contract->getPdfTemplate();

        if ($template) {
            $templateData = $this->templateService->renderTemplate($contract, $template);
            $html = $this->renderView('contract/pdf_dynamic.html.twig', [
                'contract' => $contract,
                'templateData' => $templateData,
                'public_dir' => $this->getParameter('kernel.project_dir') . '/public',
            ]);
        } else {
            $html = $this->renderView('contract/pdf.html.twig', [
                'contract' => $contract,
                'gd_installed' => extension_loaded('gd'),
            ]);
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Enabled for signatures and logos
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf('contrat-%d-%s.pdf', $contract->getId(), date('Y-m-d'));
        $pdfContent = $dompdf->output();

        if (ob_get_length())
            ob_end_clean();

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    $filename
                ),
                'Content-Length' => strlen($pdfContent),
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]
        );
    }

    /**
     * Affiche un aperçu HTML du contrat (pour l'iframe dans la modale).
     */
    #[Route('/{id}/pdf-preview', name: 'app_contract_pdf_preview', methods: ['GET'])]
    public function previewPdf(Contract $contract): Response
    {
        $template = $contract->getPdfTemplate();

        if ($template) {
            $templateData = $this->templateService->renderTemplate($contract, $template);
            return $this->render('contract/pdf_dynamic.html.twig', [
                'contract' => $contract,
                'templateData' => $templateData,
                'public_dir' => '', // Relative for browser preview
            ]);
        }

        return $this->render('contract/pdf.html.twig', [
            'contract' => $contract,
            'gd_installed' => extension_loaded('gd'),
        ]);
    }
}
