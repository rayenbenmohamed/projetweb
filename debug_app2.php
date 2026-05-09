<?php
require 'vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

try {
    $kernel = new Kernel('dev', true);
    $kernel->boot();
    $request = Request::create('/');
    $response = $kernel->handle($request);
    echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() !== 200) {
        $content = $response->getContent();
        file_put_contents('debug_response.html', $content);
        echo 'Response saved to debug_response.html' . PHP_EOL;
    } else {
        echo 'SUCCESS!' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
    echo 'CLASS: ' . get_class($e) . PHP_EOL;
    echo 'FILE: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    // Save trace separately
    file_put_contents('debug_trace.txt', $e->getTraceAsString());
    echo 'Trace saved to debug_trace.txt' . PHP_EOL;
}
