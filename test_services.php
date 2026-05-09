<?php

require_once 'vendor/autoload.php';

use App\Service\AiCommentService;
use App\Service\TranslationService;

$aiService = new AiCommentService();
$translationService = new TranslationService();

echo "Testing AiCommentService analyzeSentiment:\n";
$result = $aiService->analyzeSentiment('This is a great post!');
echo "Result: " . $result . "\n";
echo "Source: " . $aiService->getLastSource() . "\n";
echo "Error: " . ($aiService->getLastError() ?: 'none') . "\n\n";

echo "Testing TranslationService translateToEnglish:\n";
$result2 = $translationService->translateToEnglish('Bonjour le monde');
echo "Result: " . json_encode($result2) . "\n\n";

echo "Testing AiCommentService generateCommentSuggestion:\n";
$post = new stdClass();
$post->title = 'Test Post';
$post->content = 'This is a test post content.';
$result3 = $aiService->generateCommentSuggestion($post, 'Make it positive');
echo "Result: " . $result3 . "\n";
echo "Source: " . $aiService->getLastSource() . "\n";
echo "Error: " . ($aiService->getLastError() ?: 'none') . "\n";
