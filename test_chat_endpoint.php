<?php
/**
 * Test Chat Endpoint Response
 * Check what the chat endpoint returns when RAG is down
 */

echo "🔍 TESTING CHAT ENDPOINT RESPONSE\n";
echo "=================================\n\n";

// Test the chat endpoint
$chatUrl = 'http://localhost:8000/student/rag-chat';
$testMessage = "What assessments are available?";
$postData = json_encode([
    'message' => $testMessage
]);

echo "Testing: $chatUrl\n";
echo "Message: $testMessage\n";
echo "RAG Service: DOWN (stopped)\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chatUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test-token' // This will fail CSRF, but we can see the response
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "❌ Connection Error: $error\n";
} elseif ($httpCode === 419) {
    echo "⚠️  CSRF Token Error (HTTP 419) - This is expected in testing\n";
    echo "✅ Chat endpoint is accessible (Laravel is working)\n";
    echo "✅ This means Laravel can handle the request, just needs proper CSRF\n";
} elseif ($httpCode === 200) {
    echo "✅ HTTP 200 - Chat endpoint working\n";
    $data = json_decode($response, true);
    
    if ($data) {
        echo "\nResponse Data:\n";
        echo "- Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "- Message: " . substr($data['message'] ?? 'No message', 0, 100) . "...\n";
        echo "- Model Used: " . ($data['model_used'] ?? 'not set') . "\n";
        echo "- RAG Status: " . ($data['rag_status'] ?? 'not set') . "\n";
        echo "- Service Info: " . ($data['service_info']['text'] ?? 'not set') . "\n";
        echo "- Service Indicator: " . ($data['service_info']['indicator'] ?? 'not set') . "\n";
        
        if ($data['model_used'] === 'limited' && $data['rag_status'] === 'limited') {
            echo "\n✅ CORRECT: Chat uses Limited Mode fallback\n";
        } else {
            echo "\n❌ INCORRECT: Should use Limited Mode fallback\n";
        }
    } else {
        echo "❌ Failed to parse JSON response\n";
        echo "Raw response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "❌ HTTP $httpCode - Unexpected response\n";
    echo "Raw response: " . substr($response, 0, 200) . "...\n";
}

echo "\n";

// Test with a valid CSRF token (if we can get one)
echo "🔍 TESTING WITH VALID CSRF TOKEN\n";
echo "================================\n";

// First, get a valid CSRF token by visiting the login page
$loginUrl = 'http://localhost:8000/login';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode === 200 && preg_match('/name="csrf-token" content="([^"]+)"/', $loginResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "✅ Got CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Now test the chat endpoint with valid CSRF
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chatUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Status with valid CSRF: $httpCode\n";

    if ($httpCode === 200) {
        echo "✅ SUCCESS: Chat endpoint working with valid CSRF\n";
        $data = json_decode($response, true);
        
        if ($data) {
            echo "\nResponse Data:\n";
            echo "- Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "- Message: " . substr($data['message'] ?? 'No message', 0, 100) . "...\n";
            echo "- Model Used: " . ($data['model_used'] ?? 'not set') . "\n";
            echo "- RAG Status: " . ($data['rag_status'] ?? 'not set') . "\n";
            echo "- Service Info: " . ($data['service_info']['text'] ?? 'not set') . "\n";
            echo "- Service Indicator: " . ($data['service_info']['indicator'] ?? 'not set') . "\n";
            
            if ($data['model_used'] === 'limited' && $data['rag_status'] === 'limited') {
                echo "\n✅ CORRECT: Chat uses Limited Mode fallback\n";
            } else {
                echo "\n❌ INCORRECT: Should use Limited Mode fallback\n";
            }
        }
    } else {
        echo "❌ Still getting HTTP $httpCode with valid CSRF\n";
    }
} else {
    echo "❌ Could not get CSRF token from login page\n";
}

// Clean up
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n✅ CHAT ENDPOINT TEST COMPLETE!\n";
echo "===============================\n";
?>
