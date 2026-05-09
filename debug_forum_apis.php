<?php

require_once 'vendor/autoload.php';

$_SERVER['DATABASE_URL'] = 'mysql://root:@127.0.0.1:3306/forum_db?serverVersion=8.0.30&charset=utf8mb4';
$_SERVER['XAI_API_KEY'] = 'gsk_demo_key_for_testing';
$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_SECRET'] = 'fcd217f41d4e5c189eff5dd008443ab1';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$forumController = $container->get('App\Controller\ForumController');

// Test analyze sentiment
$request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
    'text' => 'This is a great post!'
]));

try {
    $response = $forumController->analyzeSentimentApi($request);
    echo "Analyze Sentiment Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "ERROR in analyzeSentimentApi: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test translate
$request2 = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
    'text' => 'Bonjour le monde'
]));

try {
    $response2 = $forumController->translateToEnglishApi($request2);
    echo "Translate Response: " . $response2->getContent() . "\n";
} catch (\Exception $e) {
    echo "ERROR in translateToEnglishApi: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test AI comment
$em = $container->get('doctrine.orm.entity_manager');
$post = $em->getRepository(\App\Entity\ForumPost::class)->findOneBy([]);
if ($post) {
    $request3 = new Request([], ['id' => $post->getId()], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
        'prompt' => 'Be helpful'
    ]));

    try {
        $response3 = $forumController->aiCommentApi($post->getId(), $request3);
        echo "AI Comment Response: " . $response3->getContent() . "\n";
    } catch (\Exception $e) {
        echo "ERROR in aiCommentApi: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
} else {
    echo "No forum post found for testing AI comment\n";
}
