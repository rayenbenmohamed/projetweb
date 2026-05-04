<?php
require 'vendor/autoload.php';
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$repo = $container->get('doctrine')->getRepository(\App\Entity\JobApplication::class);
$userRepo = $container->get('doctrine')->getRepository(\App\Entity\User::class);

$user = $userRepo->findOneBy(['email' => 'admin@syfonu.com']);
if ($user) {
    echo "User found: " . $user->getEmail() . "\n";
    $query = $repo->getQueryByRecruiter($user)->getQuery();
    $results = $query->getResult();
    echo "Recruiter apps: " . count($results) . "\n";
    
    $query2 = $repo->getQueryForCandidate($user)->getQuery();
    $results2 = $query2->getResult();
    echo "Candidate apps: " . count($results2) . "\n";
} else {
    echo "User not found\n";
}
