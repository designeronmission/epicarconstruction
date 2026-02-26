<?php
// Quick test for Turnstile verification
function testTurnstile() {
    $test_token = 'test_token'; // Use a real token from frontend
    
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => '0x4AAAAAACaElyfswRgOHiug RK5V9yD5Yz8',
        'response' => $test_token
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
}

// Run test
testTurnstile();