<?php
// Use curl to get the page and any error details
$ch = curl_init('http://localhost:8001/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode !== 200) {
    // Extract title/error from HTML
    if (preg_match('/<title>(.+?)<\/title>/s', $response, $m)) {
        echo "Title: " . html_entity_decode($m[1]) . "\n";
    }
    if (preg_match('/<h1[^>]*>(.+?)<\/h1>/s', $response, $m)) {
        echo "H1: " . strip_tags($m[1]) . "\n";
    }
    if (preg_match('/Exception[^<]*(.+?)(?:<\/div>|<\/td>)/s', $response, $m)) {
        echo "Exception: " . strip_tags($m[1]) . "\n";
    }
    // Save full response
    file_put_contents('error_page.html', $response);
    echo "Full response saved to error_page.html\n";
} else {
    echo "SUCCESS! Page loaded.\n";
}
