<?php

require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

try {
    $kernel = new Kernel('dev', true);
    $kernel->boot();
    
    $request = Request::create('/');
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content length: " . strlen($response->getContent()) . "\n";
    
    if ($response->getStatusCode() !== 200) {
        echo "Error detected!\n";
        // Try to get more debug info
        $error = error_get_last();
        if ($error) {
            echo "PHP Error: " . $error['message'] . "\n";
            echo "File: " . $error['file'] . "\n";
            echo "Line: " . $error['line'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
