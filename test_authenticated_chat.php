<?php
/**
 * Test Authenticated Chat Endpoint
 * Simulate an authenticated user making a chat request
 */

echo "🔍 TESTING AUTHENTICATED CHAT ENDPOINT\n";
echo "=====================================\n\n";

// First, let's check if we can access the student dashboard (which requires auth)
$dashboardUrl = 'http://localhost:8000/student/dashboard';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dashboardUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'auth_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'auth_cookies.txt');

$dashboardResponse = curl_exec($ch);
$dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Dashboard access (HTTP $dashboardHttpCode): ";
if ($dashboardHttpCode === 200) {
    echo "✅ Authenticated\n";
} elseif ($dashboardHttpCode === 302) {
    echo "🔄 Redirected (likely to login)\n";
} else {
    echo "❌ Error\n";
}

// Now try to get CSRF token from an authenticated page
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'auth_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'auth_cookies.txt');

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode === 200 && preg_match('/name="csrf-token" content="([^"]+)"/', $loginResponse, $matches)) {
    $csrfToken = $matches[1];
    echo "✅ Got CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Test the chat endpoint
    $chatUrl = 'http://localhost:8000/student/rag-chat';
    $testMessage = "What assessments are available?";
    $postData = json_encode([
        'message' => $testMessage
    ]);
    
    echo "\nTesting chat endpoint with CSRF token...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chatUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'auth_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'auth_cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "Chat endpoint HTTP status: $httpCode\n";

    if ($httpCode === 200) {
        echo "✅ SUCCESS: Chat endpoint working\n";
        $data = json_decode($response, true);
        
        if ($data) {
            echo "\nResponse Analysis:\n";
            echo "- Success: " . ($data['success'] ? 'true' : 'false') . "\n";
            echo "- Message: " . substr($data['message'] ?? 'No message', 0, 150) . "...\n";
            echo "- Model Used: " . ($data['model_used'] ?? 'not set') . "\n";
            echo "- RAG Status: " . ($data['rag_status'] ?? 'not set') . "\n";
            echo "- Service Info: " . ($data['service_info']['text'] ?? 'not set') . "\n";
            echo "- Service Indicator: " . ($data['service_info']['indicator'] ?? 'not set') . "\n";
            
            if ($data['model_used'] === 'limited' && $data['rag_status'] === 'limited') {
                echo "\n✅ CORRECT: Chat uses Limited Mode fallback (Database responses)\n";
            } elseif ($data['model_used'] === 'offline' && $data['rag_status'] === 'offline') {
                echo "\n❌ INCORRECT: Chat is using Offline Mode (Frontend fallback)\n";
                echo "   This means the Laravel controller is not properly falling back to database responses.\n";
            } else {
                echo "\n❓ UNKNOWN: Unexpected mode - " . $data['model_used'] . " / " . $data['rag_status'] . "\n";
            }
        } else {
            echo "❌ Failed to parse JSON response\n";
            echo "Raw response: " . substr($response, 0, 300) . "...\n";
        }
    } elseif ($httpCode === 401) {
        echo "❌ HTTP 401 - Authentication required\n";
        echo "   The user needs to be logged in to access the chat endpoint.\n";
        echo "   This explains why the JavaScript is falling back to frontend mode.\n";
    } elseif ($httpCode === 419) {
        echo "❌ HTTP 419 - CSRF token mismatch\n";
        echo "   CSRF token is invalid or expired.\n";
    } else {
        echo "❌ HTTP $httpCode - Unexpected response\n";
        echo "Raw response: " . substr($response, 0, 300) . "...\n";
    }
} else {
    echo "❌ Could not get CSRF token\n";
}

// Clean up
if (file_exists('auth_cookies.txt')) {
    unlink('auth_cookies.txt');
}

echo "\n✅ AUTHENTICATED CHAT TEST COMPLETE!\n";
echo "====================================\n";
?>
