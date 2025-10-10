<?php
/**
 * Test Limited Mode Response
 * Run with: php test_limited_mode.php
 */

// Simulate the request to see what response we get
$url = 'http://localhost:8000/student/rag-chat';
$data = [
    'message' => 'Show available assessments'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "===========================================\n";
echo "LIMITED MODE TEST\n";
echo "===========================================\n\n";

echo "Request URL: $url\n";
echo "Request Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

echo "HTTP Code: $httpCode\n\n";

if ($response) {
    $jsonResponse = json_decode($response, true);
    
    echo "Response:\n";
    echo json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($jsonResponse['mode'])) {
        echo "✅ Mode Field Present: " . $jsonResponse['mode'] . "\n";
    } else {
        echo "❌ Mode Field MISSING\n";
    }
    
    if (isset($jsonResponse['mode_name'])) {
        echo "✅ Mode Name: " . $jsonResponse['mode_name'] . "\n";
    } else {
        echo "❌ Mode Name MISSING\n";
    }
    
    if (isset($jsonResponse['mode_color'])) {
        echo "✅ Mode Color: " . $jsonResponse['mode_color'] . "\n";
    } else {
        echo "❌ Mode Color MISSING\n";
    }
    
    echo "\n";
    
    if (isset($jsonResponse['message'])) {
        echo "Message Content:\n";
        echo "─────────────────────────────────────\n";
        echo $jsonResponse['message'] . "\n";
        echo "─────────────────────────────────────\n";
    }
    
    if (isset($jsonResponse['response'])) {
        echo "\nResponse Content:\n";
        echo "─────────────────────────────────────\n";
        echo $jsonResponse['response'] . "\n";
        echo "─────────────────────────────────────\n";
    }
    
} else {
    echo "❌ No response received\n";
    echo "Error: " . curl_error($ch) . "\n";
}

echo "\n===========================================\n";
echo "Check Laravel logs for more details:\n";
echo "tail -f storage/logs/laravel.log | grep \"MODE\"\n";
echo "===========================================\n";
?>
