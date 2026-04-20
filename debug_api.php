<?php

require_once 'vendor/autoload.php';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

Debug::enable();

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$controller = $container->get('App\Controller\ApiController');

// Simuler la requête
$request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
    'firstName' => 'lwess',
    'lastName' => 'bhs',
    'email' => 'test_antigravity@gmail.com',
    'phone' => '55073735'
]));

try {
    $response = $controller->createCandidate(
        $request, 
        $container->get('doctrine.orm.entity_manager'),
        $container->get('security.user_password_hasher')
    );
    echo $response->getContent();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
