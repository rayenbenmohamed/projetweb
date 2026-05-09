<?php
$routes = ['/register', '/job/offre/', '/login'];

foreach ($routes as $route) {
    echo "\n=== $route ===\n";
    $ch = curl_init('http://localhost:8001' . $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        echo "cURL Error: $err\n";
        continue;
    }
    
    echo "HTTP: $httpCode\n";
    
    if ($httpCode == 500) {
        // Extract error from Symfony debug page
        if (preg_match('/<title>(.+?)<\/title>/s', $response, $m)) {
            $title = html_entity_decode($m[1]);
            // Clean up the title
            $title = preg_replace('/^\\.*?\\/', '', $title); // Remove namespace prefixes
            echo "Error: $title\n";
        }
        file_put_contents("error_$httpCode" . str_replace('/', '_', $route) . ".html", $response);
    }
}
