<?php

require_once __DIR__ . '/../src/Service/JobOffreAiService.php';

// Mocking HttpClient because we don't need it for local fallback tests
namespace Symfony\Contracts\HttpClient {
    interface HttpClientInterface {}
}

namespace {
    use App\Service\JobOffreAiService;

    // Use reflection or just mock the constructor if possible. 
    // Since it's a scratch script and the class is already defined in the same process 
    // with different requirements, I'll just copy the logic part I want to test.
    
    function testLocalFallback($msg) {
        $lower = mb_strtolower($msg);
        $location = null;
        
        // Match the logic in JobOffreAiService.php
        $cities = [
            'tunis', 'sfax', 'sousse', 'monastir', 'bizerte', 'gabes', 'kairouan', 'gafsa', 
            'ariana', 'ben arous', 'manouba', 'nabeul', 'zaghouan', 'beja', 'jendouka', 
            'le kef', 'siliana', 'kasserine', 'sidi bouzid', 'mahdia', 'tozeur', 'kebili', 'tataouine', 'mednine'
        ];
        
        // 1. Regex prepositions
        if (preg_match('/(?:à|a|en|dans|at|in)\s+([a-zéàèùâêîôûç\s\-]+?)(?:\s+(?:avec|pour|et|ou|,|$))/ui', $msg, $m)) {
            $location = ucwords(trim($m[1]));
        }
        
        // 2. Keyword detection
        if (!$location) {
            foreach ($cities as $city) {
                if (preg_match('/\b' . preg_quote($city, '/') . '\b/ui', $msg)) {
                    $location = ucwords($city);
                    break;
                }
            }
        }
        
        return $location;
    }

    $inputs = ["offres a sfax", "chercher travail sfax", "emploi tunis", "developpeur sousse cdi"];
    foreach ($inputs as $input) {
        echo "Input: '$input' => Detected Location: '" . testLocalFallback($input) . "'\n";
    }
}
