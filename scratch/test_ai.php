<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use App\Service\CVAnalyzerService;

// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        if (strpos($line, 'GEMINI_API_KEY=') === 0) {
            $key = trim(substr($line, 15));
            putenv("GEMINI_API_KEY=$key");
        }
    }
}

$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    die("API Key not found in .env\n");
}

$httpClient = HttpClient::create();
$service = new CVAnalyzerService($httpClient, $apiKey);

$jobDesc = "Développeur PHP Web avec expérience Symfony, MySQL, et Intelligence Artificielle.";
$cvText = "Jean Dupont. Développeur Fullstack. Expériences: 5 ans en Symfony et PHP. Compétences: MySQL, React, Gemini AI.";

echo "Testing AI Analysis...\n";
$result = $service->analyze($jobDesc, $cvText);

if (isset($result['error'])) {
    echo "ERROR: " . $result['error'] . "\n";
    if (isset($result['debug_text'])) {
        echo "Debug Content: " . $result['debug_text'] . "\n";
    }
} else {
    echo "SUCCESS!\n";
    echo "Score: " . ($result['score'] ?? 'N/A') . "\n";
    echo "Summary: " . ($result['summary'] ?? 'N/A') . "\n";
}
