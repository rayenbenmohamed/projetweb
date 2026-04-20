<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use App\Service\CVParserService;
use App\Service\CVAnalyzerService;

// Load .env
$apiKey = '';
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        if (strpos($line, 'GEMINI_API_KEY=') === 0) {
            $apiKey = trim(substr($line, 15));
        }
    }
}

if (!$apiKey) {
    die("API Key not found in .env\n");
}

$httpClient = HttpClient::create();
$cvParser = new CVParserService($httpClient, $apiKey);
$cvAnalyzer = new CVAnalyzerService($httpClient, $apiKey);

$cvUrl = "https://res.cloudinary.com/dbxfuedn2/image/upload/v1776429211/syfonu/cvs/wfgahxcaef7xxetwbkwo.pdf";
$jobDesc = "Développeur Web Fullstack avec expérience en PHP, Symfony, et intégration d'IA.";

echo "Step 1: Extracting text from PDF...\n";
$cvText = $cvParser->extractText($cvUrl);

if (str_starts_with($cvText, 'Erreur')) {
    echo "Extraction Error: $cvText\n";
    echo "Falling back to Multimodal Analysis (Vision)...\n";
    $result = $cvAnalyzer->analyzeDocument($jobDesc, $cvUrl);
} else {
    echo "Text Extracted successfully (Length: " . strlen($cvText) . " characters):\n";
    echo substr($cvText, 0, 200) . "...\n\n";
    
    echo "Step 2: Analyzing with Gemini AI...\n";
    $result = $cvAnalyzer->analyze($jobDesc, $cvText);
}

echo "\n--- RESULT ---\n";
if (isset($result['error'])) {
    echo "ERROR: " . $result['error'] . "\n";
    if (isset($result['debug_text'])) {
        echo "Debug Context: " . $result['debug_text'] . "\n";
    }
} else {
    echo "SUCCESS!\n";
    echo "Score: " . ($result['score'] ?? 'N/A') . "\n";
    echo "Summary: " . ($result['summary'] ?? 'N/A') . "\n";
    echo "Recommendation: " . ($result['recommendation'] ?? 'N/A') . "\n";
    echo "Pros: " . implode(", ", $result['verdict']['pros'] ?? []) . "\n";
    echo "Cons: " . implode(", ", $result['verdict']['cons'] ?? []) . "\n";
}
