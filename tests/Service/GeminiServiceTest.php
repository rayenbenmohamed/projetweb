<?php

namespace App\Tests\Service;

use App\Service\GeminiService;
use PHPUnit\Framework\TestCase;

class GeminiServiceTest extends TestCase
{
    public function testGenerateLocalTemplateWithBlueDefault(): void
    {
        // No API key forces local generation
        $service = new GeminiService('');
        $result = $service->generatePdfTemplate('un contrat classique');

        $this->assertArrayHasKey('header', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('footer', $result);
        
        // Default color should be blue (#1e40af)
        $this->assertStringContainsString('#1e40af', $result['header']);
    }

    public function testGenerateLocalTemplateRedColor(): void
    {
        $service = new GeminiService('');
        $result = $service->generatePdfTemplate('un contrat rouge');

        // Red color from service is #9f1239
        $this->assertStringContainsString('#9f1239', $result['header']);
    }

    public function testGenerateLocalTemplateLuxuryStyle(): void
    {
        $service = new GeminiService('');
        $result = $service->generatePdfTemplate('un contrat de luxe');

        // Luxury template has a header with background color and letter-spacing
        $this->assertStringContainsString('letter-spacing:4px', $result['header']);
        $this->assertStringContainsString('★ Contrat de Travail ★', $result['header']);
    }

    public function testGenerateLocalTemplateMinimalStyle(): void
    {
        $service = new GeminiService('');
        $result = $service->generatePdfTemplate('style minimal');

        // Minimal template header
        $this->assertStringContainsString('font-weight:300', $result['header']);
    }
}
