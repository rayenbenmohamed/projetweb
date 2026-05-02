<?php
require 'vendor/autoload.php';
use Symfony\Component\HttpClient\HttpClient;

$client = HttpClient::create();
try {
    $target = 'en';
    $text = 'Développeur PHP';
    $url = sprintf('https://lingva.ml/api/v1/auto/%s/%s', $target, rawurlencode($text));
    echo "Testing URL: $url\n";
    $response = $client->request('GET', $url);
    $statusCode = $response->getStatusCode();
    echo "Status Code: $statusCode\n";
    $content = $response->toArray();
    echo "Translation: " . ($content['translation'] ?? 'N/A') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
