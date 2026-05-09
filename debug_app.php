<?php
require 'vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();
$request = Request::create('/');
try {
    $response = $kernel->handle($request);
    echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() !== 200) {
        $content = $response->getContent();
        // Try to extract the error message from HTML
        if (preg_match('/<title>(.+?)<\/title>/', $content, $m)) {
            echo 'Title: ' . $m[1] . PHP_EOL;
        }
        if (preg_match('/Exception.*?:\s*(.+?)(?:<|$)/s', $content, $m)) {
            echo 'Error: ' . strip_tags($m[1]) . PHP_EOL;
        }
        // Save full response for inspection
        file_put_contents('debug_response.html', $content);
        echo 'Full response saved to debug_response.html' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
