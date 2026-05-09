<?php
$routes = [
    '/login',
    '/register',
    '/job/offre/',
    '/forum',
];

foreach ($routes as $route) {
    $ch = curl_init('http://localhost:8001' . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 500) ? 'OK' : 'ERROR';
    echo "$route => HTTP $httpCode ($status)\n";
}
