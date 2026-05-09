<?php
require 'vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();

$routes = ['/login', '/register', '/job/offre/'];

foreach ($routes as $route) {
    echo "\n=== Testing $route ===\n";
    $request = Request::create($route);
    try {
        $response = $kernel->handle($request);
        echo "Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() !== 200) {
            $content = $response->getContent();
            if (preg_match('/<title>(.+?)<\/title>/s', $content, $m)) {
                echo "Error: " . html_entity_decode($m[1]) . "\n";
            }
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
